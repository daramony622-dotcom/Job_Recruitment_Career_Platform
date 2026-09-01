<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobSeeker\UpdateJobSeekerSkillsRequest; // ហៅចូលមកប្រើ
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

        // ស្វែងរកតាមឈ្មោះ ឬ Category
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
            'data' => $skills
        ]);
    }

    /**
     * Get skills associated with the authenticated job seeker's profile.
     */
    public function mySkills(Request $request)
    {
        $user = $request->user();
        
        // ទាញយក skills ដែល user នេះបានជ្រើសរើសរួច
        $skills = $user->skills()->get();

        return response()->json([
            'status' => 'success',
            'data' => $skills
        ]);
    }

    /**
     * Add or update skills to the authenticated job seeker's profile.
     */
    public function updateMySkills(UpdateJobSeekerSkillsRequest $request)
    {
        // Validation ត្រូវបានធ្វើរួចជាស្រេចដោយស្វ័យប្រវត្តិនៅក្នុង UpdateJobSeekerSkillsRequest

        $user = $request->user();

        // ធ្វើការ Sync (បញ្ចូលថ្មី ឬលុបចេញបើមិនបានធីក) ជាមួយ Pivot Table របស់ User និង Skills
        // ទាញយកទិន្នន័យដែលបាន Validate រួចមកប្រើប្រាស់តាមរយៈ validated()
        $user->skills()->sync($request->validated('skill_ids'));

        return response()->json([
            'status' => 'success',
            'message' => 'Skills updated successfully in your profile',
            'data' => $user->skills()->get()
        ]);
    }
}