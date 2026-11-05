<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkoutPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'coach_id',
        'client_id',
        'match_id',
        'title',
        'description',
        'exercises',
        'plan_type',
        'schedule',
        'duration',
        'frequency',
        'notes',
        'goals',
        'status',
        'start_date',
        'end_date',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'exercises' => 'array',
            'schedule' => 'array',
            'goals' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(UserMatch::class, 'match_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WorkoutLog::class);
    }
}

