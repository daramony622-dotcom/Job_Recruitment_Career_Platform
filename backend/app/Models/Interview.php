<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Interview extends Model
{
    protected $fillable = [
        'application_id',
        'job_post_id',
        'applicant_id',
        'interviewer_id',
        'interview_type',
        'title',
        'scheduled_at',
        'duration_minutes',
        'location',
        'meeting_link',
        'notes_for_candidate',
        'internal_notes',
        'feedback',
        'result',
        'status',
        'confirmed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_post_id');
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('scheduled_at', '>', now())
                     ->whereIn('status', ['scheduled', 'confirmed']);
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('scheduled_at', '<=', now())
                     ->orWhereIn('status', ['completed', 'cancelled']);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('applicant_id', $userId)
                     ->orWhere('interviewer_id', $userId);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isUpcoming(): bool
    {
        return $this->scheduled_at->isFuture() && in_array($this->status, ['scheduled', 'confirmed']);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getEndTimeAttribute()
    {
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
    }
}