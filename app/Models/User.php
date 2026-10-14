<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'consultancy_id',
        'role',
        'phone',
        'profile_photo_path',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function consultancy(): BelongsTo
    {
        return $this->belongsTo(Consultancy::class);
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    public function isCompany(): bool
    {
        return $this->role === 'company';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }
}
