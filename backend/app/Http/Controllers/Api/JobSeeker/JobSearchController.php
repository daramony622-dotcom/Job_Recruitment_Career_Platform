<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobPostResource;
use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JobSearchController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:job_categories,id'],
            'job_type' => ['nullable', 'in:full_time,part_time,contract,internship,freelance,remote'],
            'work_mode' => ['nullable', 'in:onsite,remote,hybrid'],
            'experience_level' => ['nullable', 'in:entry,junior,mid,senior,lead,executive'],
            'city' => ['nullable', 'string', 'max:100'],
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
