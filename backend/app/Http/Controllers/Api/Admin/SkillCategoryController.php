<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SkillCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkillCategoryController extends Controller
{
    /**
     * Display a listing of skill categories.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SkillCategory::query()->withCount('skills');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $categories = $query->latest()->paginate($request->input('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ]);
    }

    /**
     * Store a newly created skill category.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:skill_categories,name'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $category = SkillCategory::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Skill category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified skill category with its skills.
     */
    public function show(SkillCategory $skillCategory): JsonResponse
    {
        $skillCategory->load(['skills' => function ($q) {
            $q->latest();
        }]);

        return response()->json([
            'status' => 'success',
            'data' => $skillCategory,
        ]);
    }

    /**
     * Update the specified skill category.
     */
    public function update(Request $request, SkillCategory $skillCategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', 'unique:skill_categories,name,' . $skillCategory->id],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $skillCategory->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Skill category updated successfully',
            'data' => $skillCategory->fresh()->loadCount('skills'),
        ]);
    }

    /**
     * Remove the specified skill category.
     */
    public function destroy(SkillCategory $skillCategory): JsonResponse
    {
        $skillCategory->skills()->update(['category_id' => null]);
        $skillCategory->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Skill category deleted successfully',
        ]);
    }

    /**
     * Toggle the active status of a skill category.
     */
    public function toggleActive(SkillCategory $skillCategory): JsonResponse
    {
        $skillCategory->update([
            'is_active' => !$skillCategory->is_active,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Skill category status updated',
            'data' => $skillCategory->fresh(),
        ]);
    }
}