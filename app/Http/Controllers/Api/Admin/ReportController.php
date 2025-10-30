<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDaily;
use App\Models\AttendanceSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Get daily dashboard (today's attendance summary)
     */
    public function dailyDashboard(Request $request)
    {
        try {
            $date = $request->has('date') ? Carbon::parse($request->date) : Carbon::today();
            $officeId = $request->office_id;

            $query = AttendanceSession::with(['user.office'])
                ->whereDate('check_in_time', $date);

            if ($officeId) {
                $query->whereHas('user', function($q) use ($officeId) {
                    $q->where('office_id', $officeId);
                });
            }

            $sessions = $query->get();

            // Count statistics
            $totalEmployees = User::where('role', 'karyawan')
                ->when($officeId, function($q) use ($officeId) {
                    $q->where('office_id', $officeId);
                })
                ->count();

            $present = $sessions->whereNotNull('check_in_time')->count();
            $late = $sessions->where('is_late', true)->count();
            $absent = $totalEmployees - $present;

            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $date->format('Y-m-d'),
                    'summary' => [
                        'total_employees' => $totalEmployees,
                        'present' => $present,
                        'late' => $late,
                        'absent' => $absent,
                    ],
                    'attendances' => $sessions->map(function($session) {
                        return [
                            'id' => $session->id,
                            'user' => [
                                'id' => $session->user->id,
                                'name' => $session->user->name,
                                'office' => $session->user->office->name,
                            ],
                            'check_in_time' => $session->check_in_time,
                            'check_out_time' => $session->check_out_time,
                            'is_late' => $session->is_late,
                            'late_minutes' => $session->late_minutes,
                            'check_in_location_status' => $session->check_in_location_status,
                            'check_out_location_status' => $session->check_out_location_status,
                            'check_in_face_score' => $session->check_in_face_score,
                            'check_out_face_score' => $session->check_out_face_score,
                            'duration_minutes' => $session->getDurationInMinutes(),
                        ];
                    })
                ]
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
     * Get monthly report
     */
    public function monthlyReport(Request $request)
    {
        try {
            $month = $request->has('month') ? $request->month : Carbon::now()->month;
            $year = $request->has('year') ? $request->year : Carbon::now()->year;
            $officeId = $request->office_id;
            $userId = $request->user_id;

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            // Get users
            $usersQuery = User::where('role', 'karyawan')
                ->with('office');

            if ($officeId) {
                $usersQuery->where('office_id', $officeId);
            }

            if ($userId) {
                $usersQuery->where('id', $userId);
            }

            $users = $usersQuery->get();

            // Calculate working days
            $workingDays = 0;
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                if (!$currentDate->isWeekend()) {
                    $workingDays++;
                }
                $currentDate->addDay();
            }

            $reportData = $users->map(function($user) use ($startDate, $endDate, $workingDays) {
                // Get daily attendance records
                $dailyRecords = AttendanceDaily::where('user_id', $user->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();

                $presentDays = $dailyRecords->where('total_valid_duration_minutes', '>', 0)->count();
                $lateDays = $dailyRecords->where('is_late', true)->count();
                $totalHours = $dailyRecords->sum('total_valid_duration_minutes') / 60;
                $overtimeHours = $dailyRecords->sum('overtime_minutes') / 60;

                // Calculate incentives
                $ontimeIncentive = $dailyRecords->sum('ontime_incentive');
                $overtimeIncentive = $dailyRecords->sum('overtime_minutes') > 0 ?
                    ($dailyRecords->sum('overtime_minutes') / 60) * 50000 : 0; // Example calculation
                $totalIncentive = $ontimeIncentive + $overtimeIncentive;

                return [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'office' => $user->office->name,
                    ],
                    'working_days' => $workingDays,
                    'present_days' => $presentDays,
                    'late_days' => $lateDays,
                    'total_hours' => round($totalHours, 2),
                    'overtime_hours' => round($overtimeHours, 2),
                    'ontime_incentive' => $ontimeIncentive,
                    'overtime_incentive' => $overtimeIncentive,
                    'total_incentive' => $totalIncentive,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => [
                        'month' => $month,
                        'year' => $year,
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'working_days' => $workingDays,
                    ],
                    'reports' => $reportData
                ]
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
     * Get weekly trend data for charts
     */
    public function weeklyTrend(Request $request)
    {
        try {
            $startDate = $request->has('start_date')
                ? Carbon::parse($request->start_date)
                : Carbon::now()->startOfMonth();

            $endDate = $request->has('end_date')
                ? Carbon::parse($request->end_date)
                : Carbon::now()->endOfMonth();

            $officeId = $request->office_id;

            // Group by week
            $weeks = [];
            $currentWeek = $startDate->copy()->startOfWeek();

            while ($currentWeek <= $endDate) {
                $weekEnd = $currentWeek->copy()->endOfWeek();
                if ($weekEnd > $endDate) {
                    $weekEnd = $endDate->copy();
                }

                $query = AttendanceDaily::whereBetween('date', [$currentWeek, $weekEnd]);

                if ($officeId) {
                    $query->whereHas('user', function($q) use ($officeId) {
                        $q->where('office_id', $officeId);
                    });
                }

                $present = $query->where('total_valid_duration_minutes', '>', 0)->count();
                $late = $query->where('is_late', true)->count();

                $weeks[] = [
                    'label' => 'Week ' . $currentWeek->weekOfMonth,
                    'present' => $present,
                    'late' => $late,
                ];

                $currentWeek->addWeek();
            }

            return response()->json([
                'success' => true,
                'data' => $weeks
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
     * Export report (will return data for Excel generation)
     */
    public function export(Request $request)
    {
        try {
            // For now, just return the monthly report data
            // In production, this would generate actual Excel file
            return $this->monthlyReport($request);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal export laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
