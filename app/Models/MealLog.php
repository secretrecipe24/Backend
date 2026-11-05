<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'meal_plan_id',
        'date',
        'meal_time',
        'meals',
        'food_items',
        'calories',
        'protein',
        'carbs',
        'fat',
        'water_intake',
        'hunger_level',
        'meal_skipped',
        'skip_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'meals' => 'array',
            'food_items' => 'array',
            'meal_skipped' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function mealPlan(): BelongsTo
    {
        return $this->belongsTo(MealPlan::class);
    }
}

