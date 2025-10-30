<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\AttendanceDaily;
use App\Models\LateEvent;
use App\Models\User;
use App\Services\AttendanceCalculatorService;
use App\Services\FaceRecognitionService;
use App\Services\GeolocationService;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    protected $geoService;
    protected $faceService;
    protected $attendanceCalc;
    protected $auditService;

    public function __construct(
        GeolocationService $geoService,
        FaceRecognitionService $faceService,
        AttendanceCalculatorService $attendanceCalc,
        AuditLogService $auditService
    ) {
        $this->geoService = $geoService;
        $this->faceService = $faceService;
        $this->attendanceCalc = $attendanceCalc;
        $this->auditService = $auditService;
    }

    /**
     * Check-in attendance
     */
    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'required|numeric',
            'photo_face' => 'required|string', // base64
            'device_info' => 'nullable|array',
            'is_early_overtime' => 'nullable|boolean',
            'overtime_reason' => 'nullable|string|required_if:is_early_overtime,true',
            'force_gps_continue' => 'nullable|boolean', // Allow poor GPS with manual review
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $office = $user->office;

            if (!$office) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki kantor yang terdaftar'
                ], 400);
            }

            // 1. Validasi Face Recognition
            $faceProfile = $user->faceProfile;
            if (!$faceProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan lakukan enroll wajah terlebih dahulu'
                ], 400);
            }

            $currentEmbedding = $this->faceService->generateEmbedding($request->photo_face);
            $faceVerification = $this->faceService->verifyFace(
                $faceProfile->embedding,
                $currentEmbedding,
                $faceProfile->liveness_threshold
            );

            if (!$faceVerification['match']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verifikasi wajah gagal. Score: ' . $faceVerification['score']
                ], 400);
            }

            // 2. Validasi Liveness
            $liveness = $this->faceService->checkLiveness($request->photo_face);
            if (!$liveness['is_live']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Liveness check gagal. Gunakan wajah asli.'
                ], 400);
            }

            // 3. Validasi GPS Accuracy
            $needsGpsReview = false;
            $forceGpsContinue = $request->force_gps_continue ?? false;
            
            if (!$this->geoService->isAccuracyAcceptable($request->accuracy)) {
                // If user hasn't confirmed to continue with poor GPS
                if (!$forceGpsContinue) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akurasi GPS tidak memenuhi syarat. Pindah ke area terbuka atau lanjutkan dengan review manual.',
                        'accuracy' => $request->accuracy,
                        'required' => 50,
                        'allow_force_continue' => true // Signal to frontend
                    ], 400);
                }
                
                // User confirmed to continue with poor GPS - flag for manual review
                $needsGpsReview = true;
            }

            // 4. Validasi Mock Location (TIDAK BOLEH dilewati - tetap strict)
            if ($request->device_info && $this->geoService->isMockLocation($request->device_info)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terdeteksi lokasi palsu (mock location). Matikan developer options.'
                ], 400);
            }

            // 5. Validasi Geofence
            $isInGeofence = $this->geoService->isWithinGeofence(
                $office,
                $request->latitude,
                $request->longitude
            );

            $locationStatus = $isInGeofence ? 'in_geofence' : 'out_geofence';

            $now = Carbon::now($office->timezone);

            // 6. Hitung valid start time
            $isEarlyOvertime = $request->is_early_overtime ?? false;
            $validStartAt = $this->attendanceCalc->calculateValidStartTime($user, $now, $isEarlyOvertime);

            // 7. Cek apakah terlambat (untuk jam tetap)
            $lateCheck = $this->attendanceCalc->isLate($user, $now);

            // 8. Create attendance session
            DB::beginTransaction();
            try {
                $session = AttendanceSession::create([
                    'user_id' => $user->id,
                    'check_in_at' => $now,
                    'check_in_latitude' => $request->latitude,
                    'check_in_longitude' => $request->longitude,
                    'check_in_accuracy' => $request->accuracy,
                    'check_in_face_score' => $faceVerification['score'],
                    'check_in_location_status' => $locationStatus,
                    'valid_start_at' => $validStartAt,
                    'is_overtime' => $isEarlyOvertime,
                    'overtime_reason' => $request->overtime_reason,
                    'device_info' => $request->device_info,
                    'needs_approval' => $needsGpsReview, // Flag for poor GPS accuracy
                ]);

                // 9. Jika terlambat, create late event
                $lateMessage = null;
                if ($lateCheck['is_late']) {
                    // Untuk jam tetap yang terlambat, perlu alasan
                    // (ini akan di-handle di frontend untuk tanya alasan & rencana)
                    $lateMessage = [
                        'is_late' => true,
                        'late_minutes' => $lateCheck['late_minutes'],
                        'need_reason' => true,
                    ];
                }

                DB::commit();

                // Audit log
                $this->auditService->logCreate('attendance_sessions', $session->id, $session->toArray());

                $response = [
                    'success' => true,
                    'message' => 'Check-in berhasil',
                    'data' => [
                        'session_id' => $session->id,
                        'check_in_at' => $session->check_in_at->toDateTimeString(),
                        'location_status' => $locationStatus,
                        'face_score' => $faceVerification['score'],
                        'valid_start_at' => $session->valid_start_at->toDateTimeString(),
                    ]
                ];

                if ($lateMessage) {
                    $response['late_info'] = $lateMessage;
                }

                return response()->json($response);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit late reason (untuk karyawan jam tetap yang terlambat)
     */
    public function submitLateReason(Request $request, $sessionId)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:10',
            'improvement_plan' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $session = AttendanceSession::findOrFail($sessionId);

        if ($session->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Hitung keterlambatan
        $lateCheck = $this->attendanceCalc->isLate($session->user, $session->check_in_at);

        if (!$lateCheck['is_late']) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi ini tidak terlambat'
            ], 400);
        }

        // Create late event
        $lateEvent = LateEvent::create([
            'user_id' => $session->user_id,
            'attendance_session_id' => $session->id,
            'date' => $session->check_in_at->format('Y-m-d'),
            'late_minutes' => $lateCheck['late_minutes'],
            'reason' => $request->reason,
            'improvement_plan' => $request->improvement_plan,
        ]);

        // Hitung total terlambat bulan ini
        $lateCountThisMonth = $this->attendanceCalc->getLateCountThisMonth(
            $session->user,
            $session->check_in_at
        );

        return response()->json([
            'success' => true,
            'message' => 'Alasan keterlambatan berhasil disimpan',
            'data' => [
                'late_count_this_month' => $lateCountThisMonth,
                'late_minutes' => $lateCheck['late_minutes'],
                'improvement_plan' => $request->improvement_plan,
            ]
        ]);
    }

    /**
     * Check-out attendance
     */
    public function checkOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'nullable|exists:attendance_sessions,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'required|numeric',
            'photo_face' => 'required|string',
            'work_detail' => 'required|string|min:10|max:500',
            'is_out_of_town' => 'nullable|boolean',
            'is_overtime_confirmed' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $office = $user->office;

            // Cari sesi aktif (belum check-out)
            if ($request->session_id) {
                $session = AttendanceSession::find($request->session_id);
            } else {
                $session = AttendanceSession::where('user_id', $user->id)
                    ->whereNull('check_out_at')
                    ->orderBy('check_in_at', 'desc')
                    ->first();
            }

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada sesi check-in aktif'
                ], 400);
            }

            // Face verification
            $faceProfile = $user->faceProfile;
            $currentEmbedding = $this->faceService->generateEmbedding($request->photo_face);
            $faceVerification = $this->faceService->verifyFace(
                $faceProfile->embedding,
                $currentEmbedding
            );

            if (!$faceVerification['match']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verifikasi wajah gagal'
                ], 400);
            }

            // Geofence check
            $isInGeofence = $this->geoService->isWithinGeofence(
                $office,
                $request->latitude,
                $request->longitude
            );

            $locationStatus = $isInGeofence ? 'in_geofence' : 'out_geofence';
            $needsApproval = !$isInGeofence;
            $isOutOfTown = $request->is_out_of_town ?? false;

            $now = Carbon::now($office->timezone);

            // Update session
            $session->update([
                'check_out_at' => $now,
                'check_out_latitude' => $request->latitude,
                'check_out_longitude' => $request->longitude,
                'check_out_accuracy' => $request->accuracy,
                'check_out_face_score' => $faceVerification['score'],
                'check_out_location_status' => $locationStatus,
                'valid_end_at' => $now,
                'work_detail' => $request->work_detail,
                'is_out_of_town' => $isOutOfTown,
                'needs_approval' => $needsApproval,
                'is_overtime' => $request->is_overtime_confirmed ?? false,
            ]);

            // Konsolidasi daily attendance
            $daily = $this->attendanceCalc->consolidateDailyAttendance(
                $user,
                $session->check_in_at
            );

            // Audit log
            $this->auditService->log('check_out', 'attendance_sessions', $session->id, null, $session->toArray());

            $response = [
                'success' => true,
                'message' => 'Check-out berhasil',
                'data' => [
                    'session_id' => $session->id,
                    'check_out_at' => $session->check_out_at->toDateTimeString(),
                    'duration_minutes' => $session->getDurationInMinutes(),
                    'valid_duration_minutes' => $session->getValidDurationInMinutes(),
                    'location_status' => $locationStatus,
                    'needs_approval' => $needsApproval,
                    'daily_summary' => [
                        'total_duration_hours' => $daily->getTotalValidDurationInHours(),
                        'target_hours' => $daily->getTargetDurationInHours(),
                        'overtime_hours' => $daily->overtime_hours,
                        'is_insufficient' => $daily->is_insufficient_duration,
                    ]
                ]
            ];

            if (!$isInGeofence) {
                $response['warning'] = 'Anda check-out di luar area kantor dan membutuhkan approval manual dari HRD untuk validasi';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance history
     */
    public function getHistory(Request $request)
    {
        $user = Auth::user();
        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $sessions = AttendanceSession::where('user_id', $user->id)
            ->whereBetween('check_in_at', [$startDate, $endDate])
            ->with(['lateEvent'])
            ->orderBy('check_in_at', 'desc')
            ->get();

        $daily = AttendanceDaily::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'sessions' => $sessions,
                'daily_summary' => $daily,
                'month_summary' => [
                    'total_days_present' => $daily->count(),
                    'total_work_hours' => $daily->sum('total_valid_duration') / 60,
                    'total_overtime_hours' => $daily->sum('overtime_hours'),
                    'total_late_count' => $daily->sum('late_count'),
                ]
            ]
        ]);
    }
}
