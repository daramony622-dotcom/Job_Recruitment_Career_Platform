<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App\Models\JobSkill;

class JobPost extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'job_posts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'category_id',
        'title',
        'slug',
        'description',
        'requirements',
        'benefits',
        'job_type',
        'work_mode',
        'experience_level',
        'location',
        'country',
        'city',
        'salary_min',
        'salary_max',
        'salary_currency',
        'salary_period',
        'is_salary_visible',
        'vacancies',
        'deadline',
        'status',
        'is_featured',
        'views_count',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'is_salary_visible' => 'boolean',
        'is_featured' => 'boolean',
        'vacancies' => 'integer',
        'views_count' => 'integer',
        'deadline' => 'date',
        'published_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Boot
    // -------------------------------------------------------------------------

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $jobPost) {
            if (empty($jobPost->slug)) {
                $jobPost->slug = self::generateUniqueSlug($jobPost->title);
            }
        });

        static::updating(function (self $jobPost) {
            if ($jobPost->isDirty('title') && ! $jobPost->isDirty('slug')) {
                $jobPost->slug = self::generateUniqueSlug($jobPost->title, $jobPost->id);
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'job_post_id');
    }

    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_jobs', 'job_post_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * The skills required for this job post.
     * This is a "rich" many-to-many relationship with extra pivot columns.
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'job_skill', 'job_post_id', 'skill_id')
                ->using(JobSkill::class)
                    ->withPivot('level', 'is_required') // ✅ FIXED: Fetches these columns
                    ->withTimestamps();                 // ✅ FIXED: Includes pivot timestamps
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class, 'job_post_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                     ->where(function ($q) {
                         $q->whereNull('deadline')
                           ->orWhere('deadline', '>=', now());
                     });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('company', fn($comp) => $comp->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['category_id'] ?? null, function ($q, $categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->when($filters['job_type'] ?? null, function ($q, $jobType) {
                $q->where('job_type', $jobType);
            })
            ->when($filters['work_mode'] ?? null, function ($q, $workMode) {
                $q->where('work_mode', $workMode);
            })
            ->when($filters['experience_level'] ?? null, function ($q, $experienceLevel) {
                $q->where('experience_level', $experienceLevel);
            })
            ->when($filters['city'] ?? null, function ($q, $city) {
                $q->where('city', 'like', "%{$city}%");
            });
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isPublished(): bool
    {
        $deadline = $this->getRawOriginal('deadline');

        return $this->status === 'published'
            && ($deadline === null || $deadline >= now()->toDateString());
    }

    private static function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug  = Str::slug($title);
        $count = 0;

        do {
            $candidate = $count === 0 ? $slug : "{$slug}-{$count}";
            $query     = self::where('slug', $candidate);

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            $exists = $query->exists();
            $count++;
        } while ($exists);

        return $candidate;
    }
}
