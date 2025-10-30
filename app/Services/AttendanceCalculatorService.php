<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\AttendanceDaily;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceCalculatorService
{
    /**
     * Konsolidasi semua sesi attendance dalam satu hari menjadi attendance_daily
     */
    public function consolidateDailyAttendance(User $user, Carbon $date): AttendanceDaily
    {
        // Ambil semua sesi yang sudah selesai (ada check-out) pada tanggal tersebut
        $sessions = AttendanceSession::where('user_id', $user->id)
            ->whereDate('check_in_at', $date)
            ->whereNotNull('check_out_at')
            ->get();

        // Hitung total durasi
        $totalActualDuration = 0;
        $totalValidDuration = 0;
        $lateCount = 0;
        $lateMinutes = 0;
        $isOutOfTown = false;

        foreach ($sessions as $session) {
            $totalActualDuration += $session->getDurationInMinutes();
            $totalValidDuration += $session->getValidDurationInMinutes();

            if ($session->lateEvent) {
                $lateCount++;
                $lateMinutes += $session->lateEvent->late_minutes;
            }

            if ($session->is_out_of_town) {
                $isOutOfTown = true;
            }
        }

        // Tentukan target durasi berdasar hari
        $dayType = $this->getDayType($date, $user);
        $targetDuration = $this->getTargetDuration($dayType);

        // Hitung lembur (detail sampai detik sesuai rumus Google Sheet)
        $overtimeHours = 0;
        $overtimeAmount = 0;

        if ($totalValidDuration > $targetDuration) {
            $overtimeMinutes = $totalValidDuration - $targetDuration;
            // Convert to hours with full precision (hours + minutes/60)
            // This matches Excel formula: HOUR + MINUTE/60 + SECOND/3600
            $overtimeHours = $overtimeMinutes / 60;
            $overtimeAmount = $overtimeHours * ($user->overtime_rate_per_hour ?? 0);
        }

        // Update atau create attendance_daily
        $daily = AttendanceDaily::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $date->format('Y-m-d'),
            ],
            [
                'total_actual_duration' => $totalActualDuration,
                'total_valid_duration' => $totalValidDuration,
                'target_duration' => $targetDuration,
                'overtime_hours' => $overtimeHours,
                'overtime_amount' => $overtimeAmount,
                'is_insufficient_duration' => $totalValidDuration < $targetDuration,
                'is_on_time' => $lateCount === 0,
                'late_count' => $lateCount,
                'late_minutes' => $lateMinutes,
                'is_holiday' => $dayType === 'holiday',
                'is_out_of_town' => $isOutOfTown,
                'day_type' => $dayType,
            ]
        );

        return $daily;
    }

    /**
     * Tentukan tipe hari (weekday, weekend, holiday)
     */
    private function getDayType(Carbon $date, User $user): string
    {
        // Cek apakah hari libur
        $holiday = \App\Models\Holiday::where('date', $date->format('Y-m-d'))
            ->where(function ($query) use ($user) {
                $query->where('is_global', true)
                    ->orWhere('office_id', $user->office_id);
            })
            ->first();

        if ($holiday) {
            return 'holiday';
        }

        // Cek apakah weekend (Sabtu = 6, Minggu = 0)
        $dayOfWeek = $date->dayOfWeek;
        if ($dayOfWeek === 0 || $dayOfWeek === 6) {
            return 'weekend';
        }

        return 'weekday';
    }

    /**
     * Dapatkan target durasi berdasar tipe hari (dalam menit)
     */
    private function getTargetDuration(string $dayType): int
    {
        if ($dayType === 'weekday') {
            return 480; // 8 jam
        }

        // Weekend dan holiday
        return 300; // 5 jam
    }

    /**
     * Hitung jam valid untuk check-in
     */
    public function calculateValidStartTime(
        User $user,
        Carbon $checkInTime,
        bool $isEarlyOvertimeConfirmed = false
    ): Carbon {
        if ($user->work_time_type === 'bebas') {
            return $checkInTime;
        }

        // Jam kerja tetap
        $schedule = $user->schedule;
        if (!$schedule || !$schedule->check_in_time) {
            return $checkInTime;
        }

        $scheduledTime = Carbon::parse($schedule->check_in_time);
        $scheduledTime->setDate(
            $checkInTime->year,
            $checkInTime->month,
            $checkInTime->day
        );

        // Jika check-in lebih awal dan tidak lembur pagi, gunakan jam masuk terjadwal
        if ($checkInTime->lt($scheduledTime) && !$isEarlyOvertimeConfirmed) {
            return $scheduledTime;
        }

        return $checkInTime;
    }

    /**
     * Cek apakah terlambat
     */
    public function isLate(User $user, Carbon $checkInTime): array
    {
        if ($user->work_time_type === 'bebas') {
            return [
                'is_late' => false,
                'late_minutes' => 0,
            ];
        }

        $schedule = $user->schedule;
        if (!$schedule || !$schedule->check_in_time) {
            return [
                'is_late' => false,
                'late_minutes' => 0,
            ];
        }

        $scheduledTime = Carbon::parse($schedule->check_in_time);
        $scheduledTime->setDate(
            $checkInTime->year,
            $checkInTime->month,
            $checkInTime->day
        );

        if ($checkInTime->gt($scheduledTime)) {
            return [
                'is_late' => true,
                'late_minutes' => $checkInTime->diffInMinutes($scheduledTime),
            ];
        }

        return [
            'is_late' => false,
            'late_minutes' => 0,
        ];
    }

    /**
     * Hitung jumlah keterlambatan dalam bulan ini
     */
    public function getLateCountThisMonth(User $user, Carbon $date): int
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        return AttendanceDaily::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('late_count', '>', 0)
            ->count();
    }
}
