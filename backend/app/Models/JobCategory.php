<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class JobCategory extends Model
{

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'icon',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Boot
    // -------------------------------------------------------------------------

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $category) {
            if (empty($category->slug)) {
                $category->slug = self::generateUniqueSlug($category->name);
            }
        });

        static::updating(function (self $category) {
            if ($category->isDirty('name') && ! $category->isDirty('slug')) {
                $category->slug = self::generateUniqueSlug($category->name, $category->id);
            }
        });
    }


    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function parent(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(JobCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function activeChildren(): HasMany
    {
        return $this->children()->where('is_active', true);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    private static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug  = Str::slug($name);
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