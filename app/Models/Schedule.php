<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'user_id',
        'check_in_time',
        'check_out_time',
        'work_hours',
        'active_days',
        'break_duration',
        'effective_from',
        'effective_until',
    ];

    protected function casts(): array
    {
        return [
            'check_in_time' => 'datetime:H:i:s',
            'check_out_time' => 'datetime:H:i:s',
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function getActiveDaysArray()
    {
        return explode(',', $this->active_days);
    }
}
