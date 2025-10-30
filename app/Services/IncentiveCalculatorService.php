<?php

namespace App\Services;

use App\Models\User;
use App\Models\AttendanceDaily;
use App\Models\IncentiveAdjustment;
use Carbon\Carbon;

class IncentiveCalculatorService
{
    /**
     * Hitung insentif tepat waktu untuk bulan tertentu
     */
    public function calculateOntimeIncentive(User $user, string $month): float
    {
        if ($user->work_time_type !== 'tetap') {
            return 0;
        }

        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Hitung jumlah hari hadir tepat waktu
        $ontimeDays = AttendanceDaily::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_on_time', true)
            ->where('day_type', 'weekday') // Hanya weekday
            ->count();

        // Hitung total keterlambatan
        $lateCount = AttendanceDaily::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('late_count', '>', 0)
            ->count();

        // Jika terlambat lebih dari 3 kali, insentif hangus
        if ($lateCount > 3) {
            return 0;
        }

        return $ontimeDays * $user->ontime_incentive_per_day;
    }

    /**
     * Hitung insentif luar kota untuk bulan tertentu
     */
    public function calculateOutOfTownIncentive(User $user, string $month): float
    {
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Hitung jumlah hari luar kota yang sudah diapprove
        $outOfTownDays = AttendanceDaily::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_out_of_town', true)
            ->count();

        return $outOfTownDays * $user->out_of_town_incentive_per_day;
    }

    /**
     * Hitung insentif hari libur untuk bulan tertentu
     */
    public function calculateHolidayIncentive(User $user, string $month): float
    {
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Hitung jumlah hari bekerja di hari libur
        $holidayWorkDays = AttendanceDaily::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_holiday', true)
            ->where('total_valid_duration', '>', 0) // Harus ada durasi kerja
            ->count();

        return $holidayWorkDays * $user->holiday_incentive_per_day;
    }

    /**
     * Konsolidasi semua insentif bulanan
     */
    public function consolidateMonthlyIncentives(User $user, string $month): array
    {
        $ontimeIncentive = $this->calculateOntimeIncentive($user, $month);
        $outOfTownIncentive = $this->calculateOutOfTownIncentive($user, $month);
        $holidayIncentive = $this->calculateHolidayIncentive($user, $month);

        // Ambil insentif manual
        $manualIncentives = IncentiveAdjustment::where('user_id', $user->id)
            ->where('month', $month)
            ->where('source', 'manual')
            ->sum('amount');

        $totalIncentive = $ontimeIncentive + $outOfTownIncentive + $holidayIncentive + $manualIncentives;

        return [
            'ontime_incentive' => $ontimeIncentive,
            'out_of_town_incentive' => $outOfTownIncentive,
            'holiday_incentive' => $holidayIncentive,
            'manual_incentives' => $manualIncentives,
            'total_incentive' => $totalIncentive,
        ];
    }

    /**
     * Generate incentive adjustments otomatis untuk bulan tertentu
     */
    public function generateAutoIncentives(User $user, string $month): void
    {
        // Hapus incentive auto yang lama untuk bulan ini (untuk re-calculate)
        IncentiveAdjustment::where('user_id', $user->id)
            ->where('month', $month)
            ->where('source', 'auto')
            ->delete();

        // Generate ontime incentive
        $ontimeIncentive = $this->calculateOntimeIncentive($user, $month);
        if ($ontimeIncentive > 0) {
            IncentiveAdjustment::create([
                'user_id' => $user->id,
                'month' => $month,
                'type' => 'tepat_waktu',
                'amount' => $ontimeIncentive,
                'reason' => 'Insentif kehadiran tepat waktu',
                'source' => 'auto',
            ]);
        }

        // Generate out of town incentive
        $outOfTownIncentive = $this->calculateOutOfTownIncentive($user, $month);
        if ($outOfTownIncentive > 0) {
            IncentiveAdjustment::create([
                'user_id' => $user->id,
                'month' => $month,
                'type' => 'luar_kota',
                'amount' => $outOfTownIncentive,
                'reason' => 'Insentif tugas luar kota',
                'source' => 'auto',
            ]);
        }

        // Generate holiday incentive
        $holidayIncentive = $this->calculateHolidayIncentive($user, $month);
        if ($holidayIncentive > 0) {
            IncentiveAdjustment::create([
                'user_id' => $user->id,
                'month' => $month,
                'type' => 'hari_libur',
                'amount' => $holidayIncentive,
                'reason' => 'Insentif bekerja di hari libur',
                'source' => 'auto',
            ]);
        }
    }
}
