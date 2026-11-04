<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'workout_plan_id',
        'date',
        'exercises',
        'performance',
        'duration',
        'calories_burned',
        'heart_rate_avg',
        'mood',
        'fatigue_level',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'exercises' => 'array',
            'performance' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function workoutPlan(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlan::class);
    }
}

