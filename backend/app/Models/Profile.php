<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'avatar',
        'headline',
        'bio',
        'phone',
        'date_of_birth',
        'gender',
        'nationality',
        'country',
        'city',
        'address',
        'linkedin_url',
        'github_url',
        'portfolio_url',
        'cv_path',
        'cv_original_name',
        'cv_uploaded_at',
        'availability',
        'expected_salary_min',
        'expected_salary_max',
        'salary_currency',
        'is_open_to_work',
        'is_profile_visible',
        'profile_views',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'cv_uploaded_at' => 'datetime',
        'expected_salary_min' => 'decimal:2',
        'expected_salary_max' => 'decimal:2',
        'is_open_to_work' => 'boolean',
        'is_profile_visible' => 'boolean',
        'profile_views' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class, 'user_id', 'user_id');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class, 'user_id', 'user_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_profile_visible', true);
    }

    public function scopeOpenToWork(Builder $query): Builder
    {
        return $query->where('is_open_to_work', true);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('headline', 'like', "%{$search}%")
                        ->orWhere('bio', 'like', "%{$search}%")
                        ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['city'] ?? null, function ($q, $city) {
                $q->where('city', 'like', "%{$city}%");
            })
            ->when($filters['availability'] ?? null, function ($q, $availability) {
                $q->where('availability', $availability);
            });
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function hasCv(): bool
    {
        return !empty($this->cv_path);
    }

    public function incrementProfileViews(): void
    {
        $this->increment('profile_views');
    }
}