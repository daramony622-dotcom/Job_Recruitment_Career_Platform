<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExperienceRequest;
use App\Http\Requests\UpdateExperienceRequest;
use App\Http\Resources\ExperienceResource;
use App\Models\Experience;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExperienceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Experience::class, 'experience');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $experiences = request()->user()->profile?->experiences()->latest()->get() ?? collect();

        return ExperienceResource::collection($experiences);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExperienceRequest $request): ExperienceResource
    {
        $profile = $request->user()->profile;

        if (!$profile) {
            abort(404, 'Profile not found. Please create a profile first.');
        }

        $experience = $profile->experiences()->create($request->validated());

        return new ExperienceResource($experience);
    }

    /**
     * Display the specified resource.
     */
    public function show(Experience $experience): ExperienceResource
    {
        return new ExperienceResource($experience);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExperienceRequest $request, Experience $experience): ExperienceResource
    {
        $experience->update($request->validated());

        return new ExperienceResource($experience);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Experience $experience): JsonResponse
    {
        $experience->delete();

        return response()->json([
            'message' => 'Experience deleted successfully.'
        ]);
    }
}