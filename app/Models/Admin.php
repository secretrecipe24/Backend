<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login' => 'datetime',
        ];
    }

    public function createdCoaches(): HasMany
    {
        return $this->hasMany(Coach::class, 'created_by');
    }

    public function verifiedSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'verified_by');
    }

    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'verified_by');
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

