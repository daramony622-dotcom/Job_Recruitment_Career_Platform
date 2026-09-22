<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    private const CANDIDATE_ROLES = ['user', 'job_seeker', 'candidate'];

    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone',
        'phone_number',
        'password',
        'role', // admin | hr | user | job_seeker
        'is_active',
        'email_verified_at',
        'google_id',
        'avatar',
        'telegram_id',
        'telegram_username',
        'telegram_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'avatar_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',   // hashes once on save. Never call Hash::make() as well.
            'is_active'         => 'boolean',
        ];
    }

    /* -------------------------------------------------------------------
     | Attributes
     |------------------------------------------------------------------ */

    // Always store emails trimmed and lowercase so login lookups match
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? strtolower(trim($value)) : $value,
        );
    }

    // Uses the profile avatar only if it is already loaded, to avoid an extra query per user
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->telegram_photo
            ?? $this->avatar
            ?? ($this->relationLoaded('profile') ? $this->profile?->avatar : null);
    }

    /* -------------------------------------------------------------------
     | Role helpers
     |------------------------------------------------------------------ */

    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            foreach ($roles as $r) {
                if ($this->hasRole($r)) {
                    return true;
                }
            }
            return false;
        }

        // 'user', 'job_seeker' and 'candidate' are interchangeable
        if (in_array($roles, self::CANDIDATE_ROLES, true) && $this->isJobSeeker()) {
            return true;
        }

        return $this->role === $roles;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isHr(): bool
    {
        return $this->role === 'hr';
    }

    public function isJobSeeker(): bool
    {
        return in_array($this->role, self::CANDIDATE_ROLES, true);
    }

    public function isUser(): bool
    {
        return $this->isJobSeeker();
    }

    public function isCandidate(): bool
    {
        return $this->isJobSeeker();
    }

    /* -------------------------------------------------------------------
     | Auth-provider helpers
     |------------------------------------------------------------------ */

    public function isTelegramUser(): bool
    {
        return ! is_null($this->telegram_id) && is_null($this->password);
    }

    public function isGoogleUser(): bool
    {
        return ! is_null($this->google_id) && is_null($this->password);
    }

    /* -------------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------ */

    public function otps(): HasMany
    {
        return $this->hasMany(PasswordOTp::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function cvs(): HasMany
    {
        return $this->hasMany(CV::class);
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'job_seeker_id');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'user_skill', 'user_id', 'skill_id')
            ->withPivot('level', 'years_of_experience')
            ->withTimestamps();
    }
}