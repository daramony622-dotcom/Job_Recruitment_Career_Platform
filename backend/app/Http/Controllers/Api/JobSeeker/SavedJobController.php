<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSavedJobRequest;
use App\Http\Resources\SavedJobResource;
use App\Models\SavedJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SavedJobController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SavedJob::class, 'saved_job');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $user = request()->user();

        $query = SavedJob::query()->with(['job', 'user']);

        if ($user->hasRole('admin')) {
            // Admin sees all saved jobs
        } else {
            // Job seekers see only their own saved jobs
            $query->where('user_id', $user->id);
        }

        $savedJobs = $query->latest()->get();

        return SavedJobResource::collection($savedJobs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSavedJobRequest $request): SavedJobResource
    {
        $data = $request->validated();
        
        $user = request()->user();
        if (!isset($data['user_id']) && !$user->hasRole('admin')) {
            $data['user_id'] = $user->id;
        }

        // Prevent duplicate saves for the same job by the same user
        $savedJob = SavedJob::firstOrCreate(
            [
                'user_id' => $data['user_id'],
                'job_post_id' => $data['job_post_id'],
            ],
            [
                'notes' => $data['notes'] ?? null,
            ]
        );

        $savedJob->load(['job', 'user']);

        return new SavedJobResource($savedJob);
    }

    /**
     * Display the specified resource.
     */
    public function show(SavedJob $savedJob): SavedJobResource
    {
        $savedJob->load(['job', 'user']);

        return new SavedJobResource($savedJob);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSavedJobRequest $request, SavedJob $savedJob): SavedJobResource
    {
        $savedJob->update($request->validated());
        $savedJob->load(['job', 'user']);

        return new SavedJobResource($savedJob);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SavedJob $savedJob): JsonResponse
    {
        $savedJob->delete();

        return response()->json([
            'message' => 'Job removed from saved list successfully.'
        ]);
    }
}