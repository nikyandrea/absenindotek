<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LateEvent extends Model
{
    protected $fillable = [
        'user_id',
        'attendance_session_id',
        'date',
        'late_minutes',
        'reason',
        'improvement_plan',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceSession()
    {
        return $this->belongsTo(AttendanceSession::class);
    }
}
