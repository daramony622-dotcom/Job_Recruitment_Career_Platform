<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Application extends Model
{
    protected $table = 'applications';

    protected $fillable = [
        'job_post_id',
        'user_id',
        'cover_letter',
        'cv_path',
        'cv_original_name',
        'status',
        'hr_notes',
        'rejection_reason',
        'shortlisted_at',
        'rejected_at',
        'hired_at',
    ];

    protected $casts = [
        'shortlisted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'hired_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class, 'application_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['rejected', 'withdrawn', 'hired']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isWithdrawn(): bool
    {
        return $this->status === 'withdrawn';
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['rejected', 'hired', 'withdrawn']);
    }
}