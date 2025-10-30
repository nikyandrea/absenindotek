<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'is_active',
        'work_time_type',
        'office_id',
        'overtime_rate_per_hour',
        'ontime_incentive_per_day',
        'out_of_town_incentive_per_day',
        'holiday_incentive_per_day',
        'annual_leave_quota',
        'annual_leave_remaining',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'overtime_rate_per_hour' => 'decimal:2',
            'ontime_incentive_per_day' => 'decimal:2',
            'out_of_town_incentive_per_day' => 'decimal:2',
            'holiday_incentive_per_day' => 'decimal:2',
        ];
    }

    // Relationships
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function schedule()
    {
        return $this->hasOne(Schedule::class);
    }

    public function faceProfile()
    {
        return $this->hasOne(FaceProfile::class);
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function attendanceDaily()
    {
        return $this->hasMany(AttendanceDaily::class);
    }

    public function lateEvents()
    {
        return $this->hasMany(LateEvent::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function incentiveAdjustments()
    {
        return $this->hasMany(IncentiveAdjustment::class);
    }

    public function deductionAdjustments()
    {
        return $this->hasMany(DeductionAdjustment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // Accessors for backward compatibility (alias)
    public function getOntimeIncentiveAttribute()
    {
        return (float) $this->ontime_incentive_per_day;
    }

    public function getOutOfTownIncentiveAttribute()
    {
        return (float) $this->out_of_town_incentive_per_day;
    }

    public function getHolidayIncentiveAttribute()
    {
        return (float) $this->holiday_incentive_per_day;
    }
}
