<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;

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

    // Relationships
    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function jobSeeker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    // Convenience scopes
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->whereHas('jobPost', fn ($q) => $q->where('company_id', $companyId));
    }
}