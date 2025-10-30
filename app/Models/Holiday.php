<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'date',
        'name',
        'office_id',
        'type',
        'is_global',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_global' => 'boolean',
        ];
    }

    // Relationships
    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
