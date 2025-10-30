<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyAdjustment extends Model
{
    protected $fillable = [
        'user_id',
        'year',
        'month',
        'type',
        'name',
        'amount',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'year' => 'integer',
            'month' => 'integer',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeForPeriod($query, $userId, $year, $month)
    {
        return $query->where('user_id', $userId)
            ->where('year', $year)
            ->where('month', $month);
    }

    public function scopeDeductions($query)
    {
        return $query->where('type', 'deduction');
    }

    public function scopeIncentives($query)
    {
        return $query->where('type', 'incentive');
    }
}
