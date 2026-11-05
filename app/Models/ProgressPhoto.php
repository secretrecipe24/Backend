<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coach_id',
        'photo_path',
        'photo_type',
        'photo_date',
        'weight',
        'notes',
        'is_private',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'photo_date' => 'date',
            'is_private' => 'boolean',
            'is_approved' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }
}

