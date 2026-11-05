<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'coach_id',
        'client_id',
        'scheduled_date',
        'completed_date',
        'notes',
        'action_items',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'datetime',
            'completed_date' => 'datetime',
            'action_items' => 'array',
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
}

