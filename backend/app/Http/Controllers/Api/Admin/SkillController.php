<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Services\SkillService;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    protected $skillService;

    public function __construct(SkillService $skillService)
    {
        $this->skillService = $skillService;
    }

    /**
     * Display a listing of the skills.
     */
    public function index(Request $request)
    {
        $skills = $this->skillService->getAllSkills($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $skills
        ]);
    }

    /**
     * Store a newly created skill in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:skills,name',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $skill = $this->skillService->createSkill($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Skill created successfully',
            'data' => $skill
        ], 201);
    }

    /**
     * Display the specified skill.
     */
    public function show($id)
    {
        $skill = $this->skillService->getSkillById($id);

        return response()->json([
            'status' => 'success',
            'data' => $skill
        ]);
    }

    /**
     * Update the specified skill in storage.
     */
    public function update(Request $request, Skill $skill)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:skills,name,' . $skill->id,
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $updatedSkill = $this->skillService->updateSkill($skill, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Skill updated successfully',
            'data' => $updatedSkill
        ]);
    }

    /**
     * Remove the specified skill from storage.
     */
    public function destroy(Skill $skill)
    {
        $this->skillService->deleteSkill($skill);

        return response()->json([
            'status' => 'success',
            'message' => 'Skill deleted successfully'
        ]);
    }
}