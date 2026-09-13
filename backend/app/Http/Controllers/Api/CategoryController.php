<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobCategory;
use App\Models\JobPost;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get active categories with real job counts and skills list for frontend browsing.
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Fetch active Skill Categories from admin
        $skillCategories = SkillCategory::query()
            ->where('is_active', true)
            ->with(['skills' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name')
            ->get();

        // 2. Fetch active Job Categories
        $jobCategories = JobCategory::query()
            ->where('is_active', true)
            ->withCount(['jobPosts as published_jobs_count' => function ($q) {
                $q->published();
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Format Skill Categories with real computed published jobs count
        $formattedSkillCategories = $skillCategories->map(function (SkillCategory $cat) {
            $skillIds = $cat->skills->pluck('id')->toArray();
            
            $jobsCount = JobPost::query()
                ->published()
                ->where(function ($q) use ($skillIds, $cat) {
                    if (!empty($skillIds)) {
                        $q->whereHas('skills', function ($sq) use ($skillIds) {
                            $sq->whereIn('skills.id', $skillIds);
                        });
                    }
                    $q->orWhereHas('category', function ($cq) use ($cat) {
                        $cq->where('name', 'like', '%' . $cat->name . '%')
                           ->orWhere('slug', $cat->slug);
                    })
                    ->orWhere('title', 'like', '%' . $cat->name . '%')
                    ->orWhere('description', 'like', '%' . $cat->name . '%');
                })
                ->distinct('job_posts.id')
                ->count('job_posts.id');

            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'title' => $cat->name,
                'slug' => $cat->slug,
                'description' => $cat->description,
                'icon' => $cat->icon,
                'type' => 'skill_category',
                'skills_count' => $cat->skills->count(),
                'jobs_count' => $jobsCount,
                'roles' => $jobsCount . ' open ' . ($jobsCount === 1 ? 'role' : 'roles'),
                'skills' => $cat->skills->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'slug' => $s->slug,
                ]),
            ];
        });

        // Format Job Categories
        $formattedJobCategories = $jobCategories->map(function (JobCategory $cat) {
            $jobsCount = (int) $cat->published_jobs_count;
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'title' => $cat->name,
                'slug' => $cat->slug,
                'description' => $cat->description,
                'icon' => $cat->icon,
                'type' => 'job_category',
                'jobs_count' => $jobsCount,
                'roles' => $jobsCount . ' open ' . ($jobsCount === 1 ? 'role' : 'roles'),
                'skills_count' => 0,
                'skills' => [],
            ];
        });

        // Return unified list prioritizing skill categories created in admin
        $allCategories = $formattedSkillCategories->concat($formattedJobCategories);

        return response()->json([
            'status' => 'success',
            'data' => [
                'categories' => $formattedSkillCategories->isNotEmpty() ? $formattedSkillCategories : $formattedJobCategories,
                'skill_categories' => $formattedSkillCategories,
                'job_categories' => $formattedJobCategories,
                'all' => $allCategories,
            ]
        ]);
    }

    /**
     * Get all active skills with their category info and jobs count.
     */
    public function skills(Request $request): JsonResponse
    {
        $skills = Skill::query()
            ->where('is_active', true)
            ->with(['skillCategory'])
            ->withCount(['jobs as published_jobs_count' => function ($q) {
                $q->published();
            }])
            ->orderBy('name')
            ->get();

        $formatted = $skills->map(function (Skill $skill) {
            return [
                'id' => $skill->id,
                'name' => $skill->name,
                'slug' => $skill->slug,
                'category_id' => $skill->category_id,
                'category_name' => $skill->skillCategory?->name ?? $skill->category ?? 'General',
                'jobs_count' => (int) $skill->published_jobs_count,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formatted,
        ]);
    }
}