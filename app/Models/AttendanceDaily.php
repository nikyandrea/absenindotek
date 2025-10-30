<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceDaily extends Model
{
    protected $table = 'attendance_daily';

    protected $fillable = [
        'user_id',
        'date',
        'total_actual_duration',
        'total_valid_duration',
        'target_duration',
        'overtime_hours',
        'overtime_amount',
        'incentive_on_time',
        'incentive_out_of_town',
        'incentive_holiday',
        'is_insufficient_duration',
        'is_on_time',
        'late_count',
        'late_minutes',
        'monthly_late_count',
        'is_holiday',
        'is_out_of_town',
        'day_type',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'overtime_hours' => 'decimal:2',
            'overtime_amount' => 'decimal:2',
            'incentive_on_time' => 'decimal:2',
            'incentive_out_of_town' => 'decimal:2',
            'incentive_holiday' => 'decimal:2',
            'is_insufficient_duration' => 'boolean',
            'is_on_time' => 'boolean',
            'is_holiday' => 'boolean',
            'is_out_of_town' => 'boolean',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function getTotalActualDurationInHours()
    {
        return round($this->total_actual_duration / 60, 2);
    }

    public function getTotalValidDurationInHours()
    {
        return round($this->total_valid_duration / 60, 2);
    }

    public function getTargetDurationInHours()
    {
        return round($this->target_duration / 60, 2);
    }
}
