<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $fillable = [
        'name',
        'timezone',
        'geofence_type',
        'radius_meters',
        'polygon_geojson',
        'address',
        'latitude',
        'longitude',
    ];

    protected $appends = ['radius'];

    protected function casts(): array
    {
        return [
            'polygon_geojson' => 'array',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    // Accessor for radius (alias for radius_meters)
    public function getRadiusAttribute()
    {
        return $this->radius_meters;
    }

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }
}
