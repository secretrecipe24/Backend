<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachReview extends Model
{
    use HasFactory;

    protected $fillable = ['coach_id', 'client_id', 'rating', 'comment'];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
