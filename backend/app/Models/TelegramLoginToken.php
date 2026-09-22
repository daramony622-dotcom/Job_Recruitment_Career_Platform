<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramLoginToken extends Model
{
    protected $fillable = [
        'token',
        'status',
        'telegram_id',
        'telegram_payload',
        'user_id',
        'ip',
        'expires_at',
    ];

    protected $casts = [
        'expires_at'        => 'datetime',
        'telegram_payload'  => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isUsable(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isFuture();
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved' && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}