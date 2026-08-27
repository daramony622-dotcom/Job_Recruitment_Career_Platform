<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Experience extends Model
{
    protected $fillable = [
        'user_id',
        'job_title',
        'company_name',
        'employment_type',
        'location',
        'work_mode',
        'start_date',
        'end_date',
        'is_current',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('is_current', false);
    }

    public function scopeChronological(Builder $query): Builder
    {
        return $query->orderBy('is_current', 'desc')->orderBy('start_date', 'desc');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function duration(): string
    {
        $start = $this->start_date;
        $end = $this->is_current ? now() : ($this->end_date ?? now());

        $years = $start->diffInYears($end);
        $months = $start->copy()->addYears($years)->diffInMonths($end);

        $parts = [];

        if ($years > 0) {
            $parts[] = $years . ' ' . (\Illuminate\Support\Str::plural('year', $years));
        }

        if ($months > 0 || $years === 0) {
            $parts[] = $months . ' ' . (\Illuminate\Support\Str::plural('month', $months));
        }

        return implode(' ', $parts);
    }
}