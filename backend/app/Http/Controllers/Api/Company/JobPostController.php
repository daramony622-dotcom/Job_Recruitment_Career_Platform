<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreJobPostRequest;
use App\Http\Requests\Company\UpdateJobPostRequest;
use App\Models\JobPost;
use App\Services\JobPostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // <-- Add this import

class JobPostController extends Controller
{
    public function __construct(
        protected JobPostService $jobPostService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'status', 'job_type', 'work_mode',
            'experience_level', 'category_id', 'is_featured',
            'sort_by', 'sort_order'
        ]);

        $companyId = Auth::user()->company?->id;

        $jobPosts = $this->jobPostService->getJobPosts(
            filters: $filters,
            companyId: $companyId,
            perPage: $request->input('per_page', 15)
        );

        return response()->json([
            'success'=>true,
            'message'=> "Job get successful!",
            "data"=> $jobPosts
        ]);
    }

    public function show(JobPost $jobPost): JsonResponse
    {
        Gate::authorize('view', $jobPost); // <-- Updated

        $jobPost->load(['company', 'category', 'skills', 'applications']);
        return response()->json($jobPost);
    }

    public function store(StoreJobPostRequest $request): JsonResponse
    {
        $jobPost = $this->jobPostService->createJobPost($request->validated());

        return response()->json([
            'message' => 'Job post created successfully.',
            'data'    => $jobPost
        ], 201);
    }

    public function update(UpdateJobPostRequest $request, JobPost $jobPost): JsonResponse
    {
        Gate::authorize('update', $jobPost); // <-- Updated

        $updated = $this->jobPostService->updateJobPost($jobPost, $request->validated());

        return response()->json([
            'message' => 'Job post updated successfully.',
            'data'    => $updated
        ]);
    }

    public function destroy(JobPost $jobPost): JsonResponse
    {
        Gate::authorize('delete', $jobPost); // <-- Updated

        $this->jobPostService->deleteJobPost($jobPost);

        return response()->json([
            'message' => 'Job post moved to trash.'
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $jobPost = JobPost::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $jobPost); // <-- Updated

        $restored = $this->jobPostService->restoreJobPost($id);

        return response()->json([
            'message' => 'Job post restored successfully.',
            'data'    => $restored
        ]);
    }

    public function toggleFeatured(JobPost $jobPost): JsonResponse
    {
        Gate::authorize('update', $jobPost); // <-- Updated

        $updated = $this->jobPostService->toggleFeatured($jobPost);

        return response()->json([
            'message' => 'Featured status updated.',
            'data'    => $updated
        ]);
    }
}