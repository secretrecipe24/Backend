<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Coach extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'certifications',
        'expertise',
        'specialties',
        'bio',
        'availability',
        'rating',
        'status',
        'suspended_at',
        'suspension_reason',
        'created_by',
        'expertise_areas',
        'coaching_styles',
        'years_experience',
        'languages',
        'client_demographics',
        'monthly_rate',
        'max_clients',
        'current_clients',
        'profile_complete',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'expertise' => 'array',
            'specialties' => 'array',
            'availability' => 'array',
            'expertise_areas' => 'array',
            'coaching_styles' => 'array',
            'languages' => 'array',
            'client_demographics' => 'array',
            'profile_complete' => 'boolean',
            'monthly_rate' => 'decimal:2',
            'rating' => 'float',
            'suspended_at' => 'datetime',
        ];
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(UserMatch::class);
    }

    public function workoutPlans(): HasMany
    {
        return $this->hasMany(WorkoutPlan::class);
    }

    public function mealPlans(): HasMany
    {
        return $this->hasMany(MealPlan::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    public function sentMessages(): MorphMany
    {
        return $this->morphMany(Message::class, 'sender', 'sender_type', 'sender_id');
    }

    public function receivedMessages(): MorphMany
    {
        return $this->morphMany(Message::class, 'receiver', 'receiver_type', 'receiver_id');
    }
}

