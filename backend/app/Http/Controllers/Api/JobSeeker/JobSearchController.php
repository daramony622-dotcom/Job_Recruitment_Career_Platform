<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Resources\JobPostResource;

class JobSearchController extends Controller
{
    public function show(JobPost $jobPost): JobPostResource
    {
        abort_unless($jobPost->isPublished(), 404);

        $jobPost->load(['company', 'category', 'skills']);

        return new JobPostResource($jobPost);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer'],
            'skill_category' => ['nullable', 'string', 'max:255'],
            'skill_category_id' => ['nullable', 'integer'],
            'skill' => ['nullable', 'string', 'max:255'],
            'skill_id' => ['nullable', 'integer'],
            'job_type' => ['nullable', 'in:full_time,part_time,contract,internship,freelance,remote'],
            'work_mode' => ['nullable', 'in:onsite,remote,hybrid'],
            'experience_level' => ['nullable', 'in:entry,junior,mid,senior,lead,executive'],
            'city' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $jobs = JobPost::query()
            ->published()
            ->filter($filters)
            ->with(['company', 'category', 'skills'])
            ->latest('published_at')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return JobPostResource::collection($jobs);
    }
}
