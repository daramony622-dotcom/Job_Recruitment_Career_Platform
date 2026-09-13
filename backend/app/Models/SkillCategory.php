<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SkillCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $cat) {
            if (empty($cat->slug)) {
                $cat->slug = self::generateUniqueSlug($cat->name);
            }
        });

        static::updating(function (self $cat) {
            if ($cat->isDirty('name') && !$cat->isDirty('slug')) {
                $cat->slug = self::generateUniqueSlug($cat->name, $cat->id);
            }
        });
    }

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class, 'category_id');
    }

    private static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $count = 0;

        do {
            $candidate = $count === 0 ? $slug : "{$slug}-{$count}";
            $query = self::where('slug', $candidate);

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            $exists = $query->exists();
            $count++;
        } while ($exists);

        return $candidate;
    }
}
