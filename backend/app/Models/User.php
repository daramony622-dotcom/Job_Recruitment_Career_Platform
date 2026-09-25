<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /*
    |--------------------------------------------------------------------------
    | Candidate Roles
    |--------------------------------------------------------------------------
    */

    private const CANDIDATE_ROLES = [
        'user',
        'job_seeker',
        'candidate',
    ];

    /*
    |--------------------------------------------------------------------------
    | Mass Assignable Fields
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone',
        'phone_number',
        'password',
        'role',
        'company_id',
        'is_active',
        'email_verified_at',
        'google_id',
        'avatar',
        'telegram_id',
        'telegram_username',
        'telegram_photo',
    ];

    /*
    |--------------------------------------------------------------------------
    | Hidden Fields
    |--------------------------------------------------------------------------
    */

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Appended Attributes
    |--------------------------------------------------------------------------
    */

    protected $appends = [
        'avatar_url',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Email Attribute
    |--------------------------------------------------------------------------
    |
    | Always save email in lowercase and without surrounding spaces.
    |
    */

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) =>
                $value !== null
                    ? strtolower(trim($value))
                    : null,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Avatar URL
    |--------------------------------------------------------------------------
    */

    public function getAvatarUrlAttribute(): ?string
    {
        /*
         * Prefer Telegram photo.
         */
        if (!empty($this->telegram_photo)) {
            return $this->telegram_photo;
        }

        /*
         * Then normal avatar.
         */
        if (!empty($this->avatar)) {
            return $this->avatar;
        }

        /*
         * Only access profile if it was already eager-loaded.
         * This prevents an extra query for every user.
         */
        if ($this->relationLoaded('profile')) {
            return $this->profile?->avatar;
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    public function hasRole(
        string|array $roles
    ): bool {
        /*
         * Multiple roles.
         */
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }

            return false;
        }

        $role = strtolower(trim($roles));

        /*
         * user / job_seeker / candidate
         * are treated as candidate roles.
         */
        if (
            in_array(
                $role,
                self::CANDIDATE_ROLES,
                true
            )
        ) {
            return $this->isJobSeeker();
        }

        return strtolower(
            trim((string) $this->role)
        ) === $role;
    }

    public function isAdmin(): bool
    {
        return strtolower(
            trim((string) $this->role)
        ) === 'admin';
    }

    public function isHr(): bool
    {
        return strtolower(
            trim((string) $this->role)
        ) === 'hr';
    }

    public function isJobSeeker(): bool
    {
        return in_array(
            strtolower(
                trim((string) $this->role)
            ),
            self::CANDIDATE_ROLES,
            true
        );
    }

    public function isUser(): bool
    {
        return $this->isJobSeeker();
    }

    public function isCandidate(): bool
    {
        return $this->isJobSeeker();
    }

    /*
    |--------------------------------------------------------------------------
    | Authentication Provider Helpers
    |--------------------------------------------------------------------------
    */

    public function isTelegramUser(): bool
    {
        return !empty($this->telegram_id);
    }

    public function isGoogleUser(): bool
    {
        return !empty($this->google_id);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function otps(): HasMany
    {
        return $this->hasMany(
            PasswordOTP::class
        );
    }

    public function profile(): HasOne
    {
        return $this->hasOne(
            Profile::class
        );
    }

    public function cvs(): HasMany
    {
        return $this->hasMany(
            CV::class
        );
    }

    public function educations(): HasMany
    {
        return $this->hasMany(
            Education::class
        );
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(
            Experience::class
        );
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }

    public function ownedCompany(): HasOne
    {
        return $this->hasOne(Company::class, 'user_id');
    }

    public function assignedCompanies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)->withTimestamps();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(
            Application::class,
            'user_id'
        );
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(
            Skill::class,
            'user_skill',
            'user_id',
            'skill_id'
        )
        ->withPivot(
            'level',
            'years_of_experience'
        )
        ->withTimestamps();
    }
}