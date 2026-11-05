<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'client_id',
        'coach_id',
        'status',
        'start_date',
        'end_date',
        'match_score',
        'selected_by_user',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'match_score' => 'float',
            'selected_by_user' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    public function workoutPlans(): HasMany
    {
        return $this->hasMany(WorkoutPlan::class, 'match_id');
    }

    public function mealPlans(): HasMany
    {
        return $this->hasMany(MealPlan::class, 'match_id');
    }
}

