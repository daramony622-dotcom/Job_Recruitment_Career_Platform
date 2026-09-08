<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobSeeker\UpdateJobSeekerSkillsRequest;
use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    /**
     * Display a listing of active skills for job seekers to choose from.
     */
    public function index(Request $request)
    {
        $query = Skill::where('is_active', true);

        // Search by name or category if provided
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $skills = $query->paginate(20);

        return response()->json([
            'status' => 'success',
            'data'   => $skills
        ]);
    }

    /**
     * Get skills associated with the authenticated job seeker's profile.
     */
    public function mySkills(Request $request)
    {
        $user = $request->user();
        
        // Retrieve skills linked to the authenticated user
        $skills = $user->skills()->get();

        return response()->json([
            'status' => 'success',
            'data'   => $skills
        ]);
    }

    /**
     * Add or update skills to the authenticated job seeker's profile.
     */
    public function updateMySkills(UpdateJobSeekerSkillsRequest $request)
    {
        $user = $request->user();

        // Sync skills using the validated data from UpdateJobSeekerSkillsRequest
        $user->skills()->sync($request->validated('skill_ids'));

        return response()->json([
            'status'  => 'success',
            'message' => 'Skills updated successfully in your profile',
            'data'    => $user->skills()->get()
        ]);
    }

    public function storeCustomSkill(Request $request)
    {
        $name = trim((string) $request->input('name'));
        $request->merge(['name' => $name]);
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $profile = $request->user()->profile;
        abort_if(!$profile, 404, 'Profile not found.');

        $customSkills = $profile->custom_skills ?? [];
        if (!collect($customSkills)->contains(fn ($skill) => strcasecmp($skill, $name) === 0)) {
            $customSkills[] = $name;
            $profile->update(['custom_skills' => array_values($customSkills)]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $profile->fresh()->custom_skills ?? [],
        ], 201);
    }

    public function destroyCustomSkill(Request $request, string $name)
    {
        $profile = $request->user()->profile;
        abort_if(!$profile, 404, 'Profile not found.');

        $customSkills = collect($profile->custom_skills ?? [])
            ->reject(fn ($skill) => strcasecmp($skill, trim($name)) === 0)
            ->values()
            ->all();
        $profile->update(['custom_skills' => $customSkills]);

        return response()->json([
            'status' => 'success',
            'data' => $customSkills,
        ]);
    }
}