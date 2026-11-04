<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'age',
        'gender',
        'fitness_goals',
        'dietary_preferences',
        'restrictions',
        'goal_weight',
        'subscription_tier',
        'subscription_status',
        'subscription_expiry',
        'dark_mode',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'fitness_goals' => 'array',
            'dietary_preferences' => 'array',
            'subscription_expiry' => 'datetime',
            'dark_mode' => 'boolean',
            'goal_weight' => 'decimal:2',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function matchesAsClient(): HasMany
    {
        return $this->hasMany(UserMatch::class, 'client_id');
    }

    public function workoutPlansAsClient(): HasMany
    {
        return $this->hasMany(WorkoutPlan::class, 'client_id');
    }

    public function mealPlansAsClient(): HasMany
    {
        return $this->hasMany(MealPlan::class, 'client_id');
    }

    public function workoutLogs(): HasMany
    {
        return $this->hasMany(WorkoutLog::class, 'client_id');
    }

    public function mealLogs(): HasMany
    {
        return $this->hasMany(MealLog::class, 'client_id');
    }

    public function sleepLogs(): HasMany
    {
        return $this->hasMany(SleepLog::class, 'client_id');
    }

    public function progressPhotos(): HasMany
    {
        return $this->hasMany(ProgressPhoto::class);
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class);
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
