<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeductionAdjustment extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'amount',
        'reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
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
    public function scopeForMonth($query, $month)
    {
        return $query->where('month', $month);
    }
}
