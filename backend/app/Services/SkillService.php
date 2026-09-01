<?php

namespace App\Services;

use App\Models\Skill;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SkillService
{
    /**
     * Get paginated list of skills with filtering options.
     */
    public function getAllSkills(array $filters = []): LengthAwarePaginator
    {
        $query = Skill::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category'])) {
            $query->ofCategory($filters['category']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create a new skill.
     */
    public function createSkill(array $data): Skill
    {
        return DB::transaction(function () use ($data) {
            return Skill::create([
                'name' => $data['name'],
                'slug' => $data['slug'] ?? null, // Handled automatically by model boot if empty
                'category' => $data['category'] ?? null,
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
        });
    }

    /**
     * Find a skill by ID.
     */
    public function getSkillById(int $id): Skill
    {
        return Skill::findOrFail($id);
    }

    /**
     * Update an existing skill.
     */
    public function updateSkill(Skill $skill, array $data): Skill
    {
        return DB::transaction(function () use ($skill, $data) {
            $skill->update($data);
            return $skill->fresh();
        });
    }

    /**
     * Delete a skill.
     */
    public function deleteSkill(Skill $skill): bool
    {
        return DB::transaction(function () use ($skill) {
            // Detach relationships before deleting to prevent orphan records in pivot tables
            $skill->jobs()->detach();
            $skill->users()->detach();

            return $skill->delete();
        });
    }
}