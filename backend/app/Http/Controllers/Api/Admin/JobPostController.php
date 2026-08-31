<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreJobPostRequest;
use App\Http\Requests\Admin\UpdateJobPostRequest;
use App\Models\JobPost;
use App\Services\JobPostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobPostController extends Controller
{
    public function __construct(
        protected JobPostService $jobPostService
    ) {}
    
    /**
     * List all job posts (Admin sees everything).
     */
    public function index(Request $request): JsonResponse
    {
        
        $filters = $request->only([
            'search', 'status', 'job_type', 'work_mode', 
            'experience_level', 'category_id', 'is_featured',
            'sort_by', 'sort_order'
        ]);

        $jobPosts = $this->jobPostService->getJobPosts(
            filters: $filters,
            companyId: null, // Admin sees ALL companies
            perPage: $request->input('per_page', 15)
        );

        return response()->json($jobPosts);
    }

    /**
     * Show a single job post (Admin can view any).
     */
    public function show(JobPost $jobPost): JsonResponse
    {
        $jobPost->load(['company', 'category', 'skills', 'applications']);
        return response()->json($jobPost);
    }


    /**
     * Create a new job post for ANY company.
     */
    public function store(StoreJobPostRequest $request): JsonResponse
    {
        $jobPost = $this->jobPostService->createJobPost($request->validated());

        return response()->json([
            'message' => 'Job post created successfully by Admin.',
            'data'    => $jobPost
        ], 201);
    }

    /**
     * Update ANY job post.
     */
    public function update(UpdateJobPostRequest $request, JobPost $jobPost): JsonResponse
    {
        $updated = $this->jobPostService->updateJobPost($jobPost, $request->validated());

        return response()->json([
            'message' => 'Job post updated successfully.',
            'data'    => $updated
        ]);
    }

    /**
     * Soft delete ANY job post.
     */
    public function destroy(JobPost $jobPost): JsonResponse
    {
        $this->jobPostService->deleteJobPost($jobPost);

        return response()->json([
            'message' => 'Job post moved to trash.'
        ]);
    }

    /**
     * Restore a soft-deleted job post.
     */
    public function restore(int $id): JsonResponse
    {
        $jobPost = $this->jobPostService->restoreJobPost($id);

        return response()->json([
            'message' => 'Job post restored successfully.',
            'data'    => $jobPost
        ]);
    }

    /**
     * Permanently delete a job post.
     */
    public function forceDelete(int $id): JsonResponse
    {
        $this->jobPostService->forceDeleteJobPost($id);

        return response()->json([
            'message' => 'Job post permanently deleted.'
        ]);
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(JobPost $jobPost): JsonResponse
    {
        $updated = $this->jobPostService->toggleFeatured($jobPost);

        return response()->json([
            'message' => 'Featured status updated.',
            'data'    => $updated
        ]);
    }
}