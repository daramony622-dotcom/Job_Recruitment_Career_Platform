<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role', // admin | hr | user
        'email_verified_at',
        'google_id',
        'avatar',
        'telegram_id',
        'telegram_username',
        'telegram_photo',
    ];

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->telegram_photo ?? $this->avatar;
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function otps()
    {
        return $this->hasMany(PasswordOTp::class);
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isHr(): bool
    {
        return $this->role === 'hr';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }
}
