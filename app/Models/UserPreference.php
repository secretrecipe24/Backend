<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fitness_goals',
        'coaching_style',
        'preferred_specialties',
        'communication_frequency',
        'communication_style',
        'available_days',
        'preferred_time_start',
        'preferred_time_end',
        'experience_level',
        'additional_notes',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'fitness_goals' => 'array',
            'preferred_specialties' => 'array',
            'available_days' => 'array',
            'is_completed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

