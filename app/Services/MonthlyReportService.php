<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\AttendanceDaily;
use App\Models\MonthlyAdjustment;
use App\Models\User;
use Carbon\Carbon;

class MonthlyReportService
{
    /**
     * Generate monthly report data for a user
     */
    public function generateMonthlyReport($userId, $year, $month)
    {
        $user = User::with('office')->findOrFail($userId);
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Get all attendance sessions for the month
        $sessions = AttendanceSession::where('user_id', $userId)
            ->whereBetween('check_in_at', [$startDate, $endDate])
            ->whereNotNull('check_out_at')
            ->orderBy('check_in_at')
            ->get();

        // Get daily attendance data
        $dailyData = AttendanceDaily::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy('date');

        // Calculate total late count in month for incentive calculation
        $monthlyLateCount = $dailyData->sum('late_count');

        // Prepare report rows
        $rows = [];
        $no = 1;
        
        $totalValidDuration = 0;
        $totalNormalDuration = 0;
        $totalOvertimeDuration = 0;
        $totalOvertimeAmount = 0;
        $totalIncentiveOnTime = 0;
        $totalIncentiveOutOfTown = 0;
        $totalIncentiveHoliday = 0;

        foreach ($sessions as $session) {
            $date = $session->check_in_at->format('Y-m-d');
            $daily = $dailyData->get($date);

            // Determine normal working hours based on day type
            $dayOfWeek = $session->check_in_at->dayOfWeek;
            $isWeekend = in_array($dayOfWeek, [0, 6]); // Sunday = 0, Saturday = 6
            $isHoliday = $daily ? $daily->is_holiday : false;
            
            // Normal duration: 8 hours (480 min) for weekday, 5 hours (300 min) for weekend/holiday
            $normalDurationMinutes = ($isWeekend || $isHoliday) ? 300 : 480;
            
            // Valid duration (from valid_start_at to valid_end_at)
            // Calculate in SECONDS for precision, then convert to minutes
            $validDurationSeconds = 0;
            if ($session->valid_start_at && $session->valid_end_at) {
                $validDurationSeconds = $session->valid_start_at->diffInSeconds($session->valid_end_at);
            }
            $validDurationMinutes = $validDurationSeconds / 60;

            // Overtime duration (valid - normal) in seconds
            $overtimeDurationSeconds = max(0, $validDurationSeconds - ($normalDurationMinutes * 60));
            $overtimeDurationMinutes = $overtimeDurationSeconds / 60;

            // Calculate overtime amount (detail sampai detik)
            // Formula Excel: =((HOUR(cell) + MINUTE(cell)/60 + SECOND(cell)/3600) * rate)
            // Equivalent PHP: overtimeSeconds / 3600 * rate
            $overtimeHours = $overtimeDurationSeconds / 3600;
            $overtimeRate = $user->overtime_rate_per_hour ?? 0;
            $overtimeAmount = $overtimeHours * $overtimeRate;

            // Incentive calculations
            $incentiveOnTime = 0;
            $incentiveOutOfTown = 0;
            $incentiveHoliday = 0;

            if ($daily) {
                // Incentive OnTime - hangus jika telat > 3x dalam sebulan
                if ($daily->is_on_time && $monthlyLateCount <= 3) {
                    $incentiveOnTime = $user->incentive_on_time ?? 10000; // Default Rp 10,000
                }

                // Incentive Out of Town
                if ($daily->is_out_of_town) {
                    $incentiveOutOfTown = $user->incentive_out_of_town ?? 50000; // Default Rp 50,000
                }

                // Incentive Holiday
                if ($daily->is_holiday || $isWeekend) {
                    $incentiveHoliday = $user->incentive_holiday ?? 25000; // Default Rp 25,000
                }
            }

            // Accumulate totals
            $totalValidDuration += $validDurationMinutes;
            $totalNormalDuration += $normalDurationMinutes;
            $totalOvertimeDuration += $overtimeDurationMinutes;
            $totalOvertimeAmount += $overtimeAmount;
            $totalIncentiveOnTime += $incentiveOnTime;
            $totalIncentiveOutOfTown += $incentiveOutOfTown;
            $totalIncentiveHoliday += $incentiveHoliday;

            $rows[] = [
                'no' => $no++,
                'date' => $session->check_in_at->format('Y-m-d'),
                'date_formatted' => $session->check_in_at->isoFormat('dddd, DD MMMM YYYY'),
                'check_in_actual' => $session->check_in_at->format('H:i:s'),
                'check_out_actual' => $session->check_out_at ? $session->check_out_at->format('H:i:s') : '-',
                'check_in_valid' => $session->valid_start_at ? $session->valid_start_at->format('H:i:s') : '-',
                'check_out_valid' => $session->valid_end_at ? $session->valid_end_at->format('H:i:s') : '-',
                'valid_duration_minutes' => $validDurationMinutes,
                'valid_duration_formatted' => $this->formatDuration($validDurationMinutes),
                'normal_duration_minutes' => $normalDurationMinutes,
                'normal_duration_formatted' => $this->formatDuration($normalDurationMinutes),
                'overtime_duration_minutes' => $overtimeDurationMinutes,
                'overtime_duration_formatted' => $this->formatDuration($overtimeDurationMinutes),
                'overtime_amount' => $overtimeAmount,
                'incentive_on_time' => $incentiveOnTime,
                'incentive_out_of_town' => $incentiveOutOfTown,
                'incentive_holiday' => $incentiveHoliday,
                'daily_report' => $session->daily_report ?? $session->work_detail ?? '-',
            ];
        }

        // Get monthly adjustments (deductions and additional incentives)
        $adjustments = MonthlyAdjustment::forPeriod($userId, $year, $month)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        // Calculate grand total
        $grandTotal = $totalOvertimeAmount + $totalIncentiveOnTime + $totalIncentiveOutOfTown + $totalIncentiveHoliday;
        
        // Add adjustments to grand total
        $totalAdditionalIncentives = 0;
        $totalDeductions = 0;
        
        foreach ($adjustments as $adjustment) {
            if ($adjustment->type === 'incentive') {
                $totalAdditionalIncentives += $adjustment->amount;
            } else {
                $totalDeductions += $adjustment->amount;
            }
        }

        $finalTotal = $grandTotal + $totalAdditionalIncentives - $totalDeductions;

        return [
            'user' => $user,
            'period' => [
                'year' => $year,
                'month' => $month,
                'month_name' => Carbon::create($year, $month)->isoFormat('MMMM YYYY'),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'rows' => $rows,
            'summary' => [
                'total_days' => count($rows),
                'total_valid_duration' => $totalValidDuration,
                'total_valid_duration_formatted' => $this->formatDuration($totalValidDuration),
                'total_normal_duration' => $totalNormalDuration,
                'total_normal_duration_formatted' => $this->formatDuration($totalNormalDuration),
                'total_overtime_duration' => $totalOvertimeDuration,
                'total_overtime_duration_formatted' => $this->formatDuration($totalOvertimeDuration),
                'total_overtime_amount' => $totalOvertimeAmount,
                'total_incentive_on_time' => $totalIncentiveOnTime,
                'total_incentive_out_of_town' => $totalIncentiveOutOfTown,
                'total_incentive_holiday' => $totalIncentiveHoliday,
                'monthly_late_count' => $monthlyLateCount,
                'incentive_on_time_status' => $monthlyLateCount > 3 ? 'HANGUS (Telat > 3x)' : 'AKTIF',
            ],
            'adjustments' => [
                'incentives' => $adjustments->where('type', 'incentive')->values(),
                'deductions' => $adjustments->where('type', 'deduction')->values(),
                'total_incentives' => $totalAdditionalIncentives,
                'total_deductions' => $totalDeductions,
            ],
            'totals' => [
                'grand_total' => $grandTotal,
                'final_total' => $finalTotal,
            ],
        ];
    }

    /**
     * Format duration in minutes to HH:MM:SS
     */
    private function formatDuration($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = floor($minutes % 60);
        $secs = round(($minutes - floor($minutes)) * 60);
        
        return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    }

    /**
     * Export monthly report to array format for Excel
     */
    public function exportToArray($userId, $year, $month)
    {
        $report = $this->generateMonthlyReport($userId, $year, $month);
        
        // Prepare Excel-compatible array
        $data = [];

        // Header
        $data[] = ['LAPORAN KEHADIRAN BULANAN'];
        $data[] = ['Nama', $report['user']->name];
        $data[] = ['Periode', $report['period']['month_name']];
        $data[] = ['Kantor', $report['user']->office->name ?? '-'];
        $data[] = []; // Empty row

        // Column headers
        $data[] = [
            'No',
            'Tanggal',
            'Check-in Aktual',
            'Check-out Aktual',
            'Check-in Valid',
            'Check-out Valid',
            'Durasi Kerja Valid',
            'Durasi Kerja Normal',
            'Durasi Lembur',
            'Nominal Lembur',
            'Insentif On Time',
            'Insentif Luar Kota',
            'Insentif Hari Libur',
            'Daily Report',
        ];

        // Data rows
        foreach ($report['rows'] as $row) {
            $data[] = [
                $row['no'],
                $row['date_formatted'],
                $row['check_in_actual'],
                $row['check_out_actual'],
                $row['check_in_valid'],
                $row['check_out_valid'],
                $row['valid_duration_formatted'],
                $row['normal_duration_formatted'],
                $row['overtime_duration_formatted'],
                $row['overtime_amount'],
                $row['incentive_on_time'],
                $row['incentive_out_of_town'],
                $row['incentive_holiday'],
                $row['daily_report'],
            ];
        }

        // Summary row
        $data[] = []; // Empty row
        $data[] = [
            '',
            'TOTAL',
            '',
            '',
            '',
            '',
            $report['summary']['total_valid_duration_formatted'],
            $report['summary']['total_normal_duration_formatted'],
            $report['summary']['total_overtime_duration_formatted'],
            $report['summary']['total_overtime_amount'],
            $report['summary']['total_incentive_on_time'],
            $report['summary']['total_incentive_out_of_town'],
            $report['summary']['total_incentive_holiday'],
            '',
        ];

        $data[] = []; // Empty row
        $data[] = ['GRAND TOTAL', $report['totals']['grand_total']];

        // Adjustments section
        if ($report['adjustments']['incentives']->count() > 0 || $report['adjustments']['deductions']->count() > 0) {
            $data[] = []; // Empty row
            $data[] = ['INSENTIF TAMBAHAN'];

            foreach ($report['adjustments']['incentives'] as $adj) {
                $data[] = [$adj->name, $adj->amount, $adj->notes];
            }

            $data[] = []; // Empty row
            $data[] = ['POTONGAN'];

            foreach ($report['adjustments']['deductions'] as $adj) {
                $data[] = [$adj->name, $adj->amount, $adj->notes];
            }

            $data[] = []; // Empty row
            $data[] = ['TOTAL AKHIR', $report['totals']['final_total']];
        }

        // Note about incentive on time
        if ($report['summary']['monthly_late_count'] > 3) {
            $data[] = []; // Empty row
            $data[] = ['CATATAN: Insentif On Time HANGUS karena telat lebih dari 3x dalam sebulan (' . $report['summary']['monthly_late_count'] . 'x)'];
        }

        return $data;
    }
}
