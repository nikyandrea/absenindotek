<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceSession extends Model
{
    protected $fillable = [
        'user_id',
        'check_in_at',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_accuracy',
        'check_in_face_score',
        'check_in_photo_path',
        'check_out_at',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_accuracy',
        'check_out_face_score',
        'check_out_photo_path',
        'check_in_location_status',
        'check_out_location_status',
        'is_out_of_town',
        'needs_approval',
        'is_approved',
        'approved_by',
        'approved_at',
        'work_detail',
        'daily_report',
        'is_overtime',
        'overtime_reason',
        'valid_start_at',
        'valid_end_at',
        'device_info',
    ];

    protected function casts(): array
    {
        return [
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'valid_start_at' => 'datetime',
            'valid_end_at' => 'datetime',
            'approved_at' => 'datetime',
            'check_in_latitude' => 'decimal:8',
            'check_in_longitude' => 'decimal:8',
            'check_out_latitude' => 'decimal:8',
            'check_out_longitude' => 'decimal:8',
            'check_in_accuracy' => 'decimal:2',
            'check_out_accuracy' => 'decimal:2',
            'check_in_face_score' => 'decimal:2',
            'check_out_face_score' => 'decimal:2',
            'is_out_of_town' => 'boolean',
            'needs_approval' => 'boolean',
            'is_approved' => 'boolean',
            'is_overtime' => 'boolean',
            'device_info' => 'array',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function lateEvent()
    {
        return $this->hasOne(LateEvent::class);
    }

    // Helper methods
    public function getDurationInMinutes()
    {
        if (!$this->check_out_at) {
            return 0;
        }
        return $this->check_in_at->diffInMinutes($this->check_out_at);
    }

    public function getValidDurationInMinutes()
    {
        if (!$this->valid_end_at || !$this->valid_start_at) {
            return 0;
        }
        // Return in minutes but calculate from seconds for precision
        return $this->valid_start_at->diffInSeconds($this->valid_end_at) / 60;
    }
    
    public function getValidDurationInSeconds()
    {
        if (!$this->valid_end_at || !$this->valid_start_at) {
            return 0;
        }
        return $this->valid_start_at->diffInSeconds($this->valid_end_at);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('check_out_at');
    }

    public function scopePending($query)
    {
        return $query->whereNull('check_out_at');
    }

    public function scopeNeedsApproval($query)
    {
        return $query->where('needs_approval', true)
            ->whereNull('is_approved');
    }
}
