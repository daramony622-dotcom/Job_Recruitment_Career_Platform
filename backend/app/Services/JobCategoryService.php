<?php

namespace App\Services;

use App\Models\JobCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class JobCategoryService
{
    private const CACHE_KEY = 'job_categories_tree';
    private const CACHE_TTL = 3600; // 1 hour

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return JobCategory::with('parent')
            ->when(
                ! empty($filters['search']),
                fn ($q) => $q->where('name', 'like', "%{$filters['search']}%")
                            ->orWhere('slug', 'like', "%{$filters['search']}%")
            )
            ->when(
                isset($filters['is_active']),
                fn ($q) => $q->where('is_active', $filters['is_active'])
            )
            ->when(
                isset($filters['parent_id']),
                fn ($q) => $q->where('parent_id', $filters['parent_id'])
            )
            ->ordered()
            ->paginate($perPage);
    }

    public function getTree(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return JobCategory::with(['children' => fn ($q) => $q->active()->ordered()])
                ->active()
                ->roots()
                ->ordered()
                ->get();
        });
    }

    public function getActiveRoots(): Collection
    {
        return JobCategory::active()->roots()->ordered()->get(['id', 'name']);
    }

    public function findOrFail(int $id): JobCategory
    {
        return JobCategory::findOrFail($id);
    }

    // -------------------------------------------------------------------------
    // Write
    // -------------------------------------------------------------------------

    public function create(array $data): JobCategory
    {
        $category = DB::transaction(function () use ($data) {
            return JobCategory::create($data);
        });

        $this->clearCache();

        return $category;
    }

    /**
     * Update category safely accepts either JobCategory model or int/string ID.
     */
    public function update(JobCategory|int|string $category, array $data): JobCategory
    {
        if (! $category instanceof JobCategory) {
            $category = $this->findOrFail((int) $category);
        }

        DB::transaction(function () use ($category, $data) {
            $category->update($data);
        });

        $this->clearCache();

        return $category->fresh();
    }

    /**
     * Delete category safely accepts either JobCategory model or int/string ID.
     */
    public function delete(JobCategory|int|string $category): void
    {
        if (! $category instanceof JobCategory) {
            $category = $this->findOrFail((int) $category);
        }

        if ($category->hasChildren()) {
            throw new \LogicException(
                "Cannot delete category \"{$category->name}\" because it has child categories. " .
                "Reassign or delete the children first."
            );
        }

        DB::transaction(fn () => $category->delete());

        $this->clearCache();
    }

    public function reorder(array $items): void
    {
        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                JobCategory::where('id', $item['id'])
                    ->update(['sort_order' => $item['sort_order']]);
            }
        });

        $this->clearCache();
    }

    public function toggleActive(JobCategory|int|string $category): JobCategory
    {
        if (! $category instanceof JobCategory) {
            $category = $this->findOrFail((int) $category);
        }

        $category->update(['is_active' => ! $category->is_active]);
        $this->clearCache();

        return $category->fresh();
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    private function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}