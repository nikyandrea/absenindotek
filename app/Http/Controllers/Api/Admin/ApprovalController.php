<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\LeaveRequest;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Get pending checkout approvals (out of geofence)
     */
    public function pendingCheckouts(Request $request)
    {
        try {
            // Get checkouts that are out of geofence OR needs GPS approval
            $sessions = AttendanceSession::with(['user.office'])
                ->whereNotNull('check_out_at')
                ->where(function($query) {
                    $query->where('check_out_location_status', 'out_of_geofence')
                          ->orWhere('needs_approval', true);
                })
                ->where('checkout_approval_status', 'pending')
                ->orderBy('check_out_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $sessions->map(function($session) {
                    return [
                        'id' => $session->id,
                        'user' => [
                            'id' => $session->user->id,
                            'name' => $session->user->name,
                            'office' => $session->user->office ? $session->user->office->name : 'N/A',
                        ],
                        'date' => $session->check_in_at ? $session->check_in_at->format('Y-m-d') : null,
                        'check_in_at' => $session->check_in_at,
                        'check_in_location_status' => $session->check_in_location_status,
                        'check_in_gps_accuracy' => $session->check_in_gps_accuracy,
                        'check_out_at' => $session->check_out_at,
                        'check_out_location_status' => $session->check_out_location_status,
                        'check_out_latitude' => $session->check_out_latitude,
                        'check_out_longitude' => $session->check_out_longitude,
                        'is_out_of_town' => $session->is_out_of_town,
                        'needs_approval' => $session->needs_approval,
                        'work_detail' => $session->work_detail,
                        'duration_minutes' => $session->actual_duration_minutes,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve checkout
     */
    public function approveCheckout(Request $request, $id)
    {
        try {
            $session = AttendanceSession::findOrFail($id);

            if ($session->checkout_approval_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Checkout sudah diproses sebelumnya'
                ], 400);
            }

            $session->update([
                'checkout_approval_status' => 'approved',
                'checkout_approved_by' => Auth::id(),
                'checkout_approved_at' => now(),
            ]);

            // Log approval
            $this->auditLogService->logApprove(
                'attendance_session',
                $session->id,
                'Checkout out-of-geofence disetujui'
            );

            return response()->json([
                'success' => true,
                'message' => 'Checkout berhasil disetujui'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui checkout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject checkout
     */
    public function rejectCheckout(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $session = AttendanceSession::findOrFail($id);

            if ($session->checkout_approval_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Checkout sudah diproses sebelumnya'
                ], 400);
            }

            $session->update([
                'checkout_approval_status' => 'rejected',
                'checkout_approved_by' => Auth::id(),
                'checkout_approved_at' => now(),
                'checkout_rejection_reason' => $request->reason,
            ]);

            // Log rejection
            $this->auditLogService->logReject(
                'attendance_session',
                $session->id,
                'Checkout ditolak: ' . $request->reason
            );

            return response()->json([
                'success' => true,
                'message' => 'Checkout berhasil ditolak'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak checkout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending leave requests
     */
    public function pendingLeaves(Request $request)
    {
        try {
            $leaves = LeaveRequest::with('user')
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $leaves->map(function($leave) {
                    return [
                        'id' => $leave->id,
                        'user' => [
                            'id' => $leave->user->id,
                            'name' => $leave->user->name,
                            'office' => $leave->user->office->name,
                        ],
                        'type' => $leave->leave_type,
                        'start_date' => $leave->start_date,
                        'end_date' => $leave->end_date,
                        'days_count' => $leave->duration_days,
                        'reason' => $leave->reason,
                        'attachment_path' => $leave->attachment_path,
                        'created_at' => $leave->created_at,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve leave request
     */
    public function approveLeave(Request $request, $id)
    {
        try {
            $leave = LeaveRequest::with('user')->findOrFail($id);

            if ($leave->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cuti/izin sudah diproses sebelumnya'
                ], 400);
            }

            // For annual leave, deduct from user's quota
            if ($leave->leave_type === 'cuti_tahunan') {
                $user = $leave->user;
                
                // Check if quota is sufficient
                if ($user->annual_leave_remaining < $leave->duration_days) {
                    return response()->json([
                        'success' => false,
                        'message' => "Quota cuti tidak cukup. Sisa: {$user->annual_leave_remaining} hari, dibutuhkan: {$leave->duration_days} hari"
                    ], 400);
                }

                // Deduct quota
                $user->annual_leave_remaining -= $leave->duration_days;
                $user->save();
            }

            $leave->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Log approval
            $this->auditLogService->logApprove(
                'leave_request',
                $leave->id,
                "Cuti/izin {$leave->leave_type} disetujui ({$leave->duration_days} hari)"
            );

            return response()->json([
                'success' => true,
                'message' => 'Cuti/izin berhasil disetujui' . ($leave->leave_type === 'cuti_tahunan' ? ". Quota dipotong: {$leave->duration_days} hari" : '')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui cuti/izin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject leave request
     */
    public function rejectLeave(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $leave = LeaveRequest::findOrFail($id);

            if ($leave->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cuti/izin sudah diproses sebelumnya'
                ], 400);
            }

            $leave->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $request->reason,
            ]);

            // Log rejection
            $this->auditLogService->logReject(
                'leave_request',
                $leave->id,
                'Cuti/izin ditolak: ' . $request->reason
            );

            return response()->json([
                'success' => true,
                'message' => 'Cuti/izin berhasil ditolak'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak cuti/izin',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
