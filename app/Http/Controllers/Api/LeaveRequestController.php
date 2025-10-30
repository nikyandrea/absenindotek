<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Get user's leave requests with quota info
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            $query = LeaveRequest::where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by type
            if ($request->has('type')) {
                $query->where('leave_type', $request->type);
            }

            $leaveRequests = $query->get();

            // Get quota info
            $quotaInfo = [
                'annual_quota' => $user->annual_leave_quota,
                'remaining' => $user->annual_leave_remaining,
                'used' => $user->annual_leave_quota - $user->annual_leave_remaining,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'leave_requests' => $leaveRequests,
                    'quota' => $quotaInfo
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data cuti: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new leave request
     */
    public function store(Request $request)
    {
        try {
            // Base validation rules
            $rules = [
                'leave_type' => 'required|in:cuti_tahunan,izin,sakit',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048' // 2MB max
            ];

            // Add reason validation only for izin and sakit
            $leaveType = $request->input('leave_type');
            if ($leaveType === 'izin' || $leaveType === 'sakit') {
                $rules['reason'] = 'required|string|min:10|max:500';
            } else {
                // Optional for cuti_tahunan
                $rules['reason'] = 'nullable|string|max:500';
            }

            $validated = $request->validate($rules);

            $user = $request->user();

            // Calculate duration in days (excluding weekends)
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $duration = 0;

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // Count only weekdays (Mon-Fri)
                if ($date->isWeekday()) {
                    $duration++;
                }
            }

            // Validate quota for annual leave
            if ($validated['leave_type'] === 'cuti_tahunan') {
                if ($user->annual_leave_remaining < $duration) {
                    return response()->json([
                        'success' => false,
                        'message' => "Quota cuti tidak cukup. Sisa: {$user->annual_leave_remaining} hari, dibutuhkan: {$duration} hari"
                    ], 400);
                }
            }

            // Handle file upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . $user->id . '_' . $file->getClientOriginalName();
                $attachmentPath = $file->storeAs('leave-attachments', $filename, 'public');
            }

            // Create leave request
            $leaveRequest = LeaveRequest::create([
                'user_id' => $user->id,
                'leave_type' => $validated['leave_type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'duration_days' => $duration,
                'reason' => $validated['reason'] ?? null, // Allow null for cuti_tahunan
                'attachment_path' => $attachmentPath,
                'status' => 'pending',
            ]);

            // Audit log
            $this->auditLogService->log(
                'leave_request_created',
                'leave_requests',
                $leaveRequest->id,
                null,
                $leaveRequest->toArray()
            );

            // Create friendly message based on leave type
            $typeNames = [
                'cuti_tahunan' => 'Cuti Tahunan',
                'izin' => 'Izin',
                'sakit' => 'Sakit'
            ];
            $typeName = $typeNames[$validated['leave_type']] ?? 'Cuti/Izin';

            return response()->json([
                'success' => true,
                'message' => "Pengajuan {$typeName} berhasil dibuat dan menunggu persetujuan",
                'data' => $leaveRequest
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pengajuan cuti: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leave request detail
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $leaveRequest = LeaveRequest::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$leaveRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan cuti tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $leaveRequest
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail cuti: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel leave request (only if pending)
     */
    public function cancel(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $leaveRequest = LeaveRequest::where('user_id', $user->id)
                ->where('id', $id)
                ->where('status', 'pending')
                ->first();

            if (!$leaveRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan cuti tidak ditemukan atau sudah diproses'
                ], 404);
            }

            $oldData = $leaveRequest->toArray();
            $leaveRequest->status = 'cancelled';
            $leaveRequest->save();

            // Audit log
            $this->auditLogService->log(
                'leave_request_cancelled',
                'leave_requests',
                $leaveRequest->id,
                $oldData,
                $leaveRequest->toArray()
            );

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan cuti berhasil dibatalkan'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan cuti: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get quota summary
     */
    public function quota(Request $request)
    {
        try {
            $user = $request->user();
            
            // Get approved leaves this year
            $approvedLeaves = LeaveRequest::where('user_id', $user->id)
                ->where('leave_type', 'cuti_tahunan')
                ->where('status', 'approved')
                ->whereYear('start_date', Carbon::now()->year)
                ->sum('duration_days');

            // Get pending leaves
            $pendingLeaves = LeaveRequest::where('user_id', $user->id)
                ->where('leave_type', 'cuti_tahunan')
                ->where('status', 'pending')
                ->sum('duration_days');

            return response()->json([
                'success' => true,
                'data' => [
                    'annual_quota' => $user->annual_leave_quota,
                    'remaining' => $user->annual_leave_remaining,
                    'used' => $approvedLeaves,
                    'pending' => $pendingLeaves,
                    'available' => $user->annual_leave_remaining - $pendingLeaves
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil info quota: ' . $e->getMessage()
            ], 500);
        }
    }
}
