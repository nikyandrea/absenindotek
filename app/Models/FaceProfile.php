<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceProfile extends Model
{
    protected $fillable = [
        'user_id',
        'embedding',
        'liveness_threshold',
        'enrollment_photo_count',
    ];

    protected function casts(): array
    {
        return [
            'liveness_threshold' => 'decimal:2',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function getEmbeddingArray()
    {
        return json_decode($this->embedding, true);
    }

    public function setEmbeddingArray(array $embedding)
    {
        $this->embedding = json_encode($embedding);
    }
}
