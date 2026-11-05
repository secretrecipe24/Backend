<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Measurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'measurement_date',
        'weight',
        'height',
        'bmi',
        'body_fat_percentage',
        'chest',
        'waist',
        'hips',
        'thighs',
        'arms',
        'forearms',
        'calves',
        'neck',
        'shoulders',
        'strength_records',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'measurement_date' => 'date',
            'strength_records' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

