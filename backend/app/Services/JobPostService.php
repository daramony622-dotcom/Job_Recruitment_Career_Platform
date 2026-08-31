<?php

namespace App\Services;

use App\Models\JobPost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class JobPostService
{
    /**
     * Format skills data into pivot-ready associative array.
     */
    private function formatPivotData(array $skills): array
    {
        $pivotData = [];
        foreach ($skills as $skill) {
            if (is_numeric($skill)) {
                $pivotData[(int) $skill] = [
                    'level'       => null,
                    'is_required' => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            } elseif (is_array($skill) && isset($skill['id'])) {
                $pivotData[$skill['id']] = [
                    'level'       => $skill['level'] ?? null,
                    'is_required' => filter_var($skill['is_required'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }
        return $pivotData;
    }

    /**
     * Get paginated list of job posts with optional filters.
     */
    public function getJobPosts(array $filters = [], ?int $companyId = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = JobPost::with(['company', 'category', 'skills']);

        // Scope by company (for Company controllers)
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        // Apply filters (search, type, mode, etc.)
        $query->filter($filters);
        
        // Sorting
        $allowedSorts = ['created_at', 'title', 'deadline', 'salary_min', 'views_count', 'is_featured'];
        $sortBy = in_array($filters['sort_by'] ?? null, $allowedSorts, true)
            ? $filters['sort_by']
            : 'created_at';
        $sortOrder = ($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate(max(1, min($perPage, 100)));
    }

    /**
     * Get a single job post with all relations.
     */
    public function getJobPost(int $id): JobPost
    {
        return JobPost::with(['company', 'category', 'skills', 'applications'])
                    ->findOrFail($id);
    }

    /**
     * Create a new job post with skills.
     */
    public function createJobPost(array $data): JobPost
    {
        return DB::transaction(function () use ($data) {
            // 1. Extract skills and remove from main data
            $skillsData = $data['skills'] ?? [];
            unset($data['skills']);

            // 2. Auto-set published_at if status is 'published'
            if (isset($data['status']) && $data['status'] === 'published' && !isset($data['published_at'])) {
                $data['published_at'] = now();
            }

            // 3. Create the job post (slug is auto-generated in the model's boot)
            $jobPost = JobPost::create($data);

            // 4. Attach skills with pivot data
            if (!empty($skillsData)) {
                $jobPost->skills()->sync($this->formatPivotData($skillsData));
            }

            return $jobPost->fresh()->load(['company', 'category', 'skills']);
        });
    }

    /**
     * Update an existing job post and its skills.
     */
    public function updateJobPost(int|JobPost $jobPost, array $data): JobPost
    {
        if ($jobPost instanceof JobPost) {
            $jobPostModel = $jobPost;
        } else {
            $jobPostModel = JobPost::findOrFail($jobPost);
        }

        return DB::transaction(function () use ($jobPostModel, $data) {
            // 1. Extract skills and remove from main data
            $skillsData = $data['skills'] ?? null;
            unset($data['skills']);

            // 2. Auto-set published_at if status is changing to 'published'
            if (isset($data['status']) && $data['status'] === 'published' && $jobPostModel->status !== 'published') {
                $data['published_at'] = now();
            }

            // 3. Update the job post (slug is auto-updated in model's boot if title changes)
            $jobPostModel->update($data);

            // 4. Sync skills with pivot data (if provided)
            if ($skillsData !== null) {
                $jobPostModel->skills()->sync($this->formatPivotData($skillsData));
            }

            return $jobPostModel->fresh()->load(['company', 'category', 'skills']);
        });
    }

    /**
     * Soft delete a job post.
     */
    public function deleteJobPost(int|JobPost $jobPost): bool
    {
        if ($jobPost instanceof JobPost) {
            return $jobPost->delete();
        }
        return JobPost::findOrFail($jobPost)->delete();
    }

    /**
     * Restore a soft-deleted job post.
     */
    public function restoreJobPost(int $id): JobPost
    {
        $jobPost = JobPost::withTrashed()->findOrFail($id);
        $jobPost->restore();
        return $jobPost->fresh();
    }

    /**
     * Permanently force delete a job post (use with caution).
     */
    public function forceDeleteJobPost(int $id): bool
    {
        $jobPost = JobPost::withTrashed()->findOrFail($id);
        return $jobPost->forceDelete();
    }

    /**
     * Toggle the 'featured' status.
     */
    public function toggleFeatured(int|JobPost $jobPost): JobPost
    {
        if ($jobPost instanceof JobPost) {
            $jobPostModel = $jobPost;
        } else {
            $jobPostModel = JobPost::findOrFail($jobPost);
        }

        $jobPostModel->is_featured = !$jobPostModel->is_featured;
        $jobPostModel->save();

        return $jobPostModel->fresh();
    }

    /**
     * Increment view count.
     */
    public function incrementViews(int|JobPost $jobPost): void
    {
        if ($jobPost instanceof JobPost) {
            $jobPost->increment('views_count');
        } else {
            JobPost::where('id', $jobPost)->increment('views_count');
        }
    }
}
