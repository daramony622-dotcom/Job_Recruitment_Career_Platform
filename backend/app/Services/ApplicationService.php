<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Job;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    /**
     * Get paginated applications for a specific job seeker.
     */
    public function getJobSeekerApplications(int $userId, array $filters = []): LengthAwarePaginator
    {
        return Application::with(['job.company', 'job.category'])
            ->where('user_id', $userId)
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get paginated applications for a company's job postings (HR view).
     */
    public function getCompanyApplications(int $companyId, array $filters = []): LengthAwarePaginator
    {
        return Application::with(['job', 'user.profile', 'user.skills'])
            ->whereHas('job', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->when($filters['job_post_id'] ?? null, function ($query, $jobId) {
                $query->where('job_post_id', $jobId);
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Submit a new job application as a job seeker.
     */
    public function submitApplication(int $userId, array $data): Application
    {
        $job = Job::findOrFail($data['job_post_id']);

        // Check if the job is published and active
        if (! $job->isPublished()) {
            throw ValidationException::withMessages([
                'job_post_id' => ['This job is no longer accepting applications.'],
            ]);
        }

        // Check for duplicate application
        $existing = Application::where('job_post_id', $job->id)
            ->where('user_id', $userId)
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'job_post_id' => ['You have already applied for this job position.'],
            ]);
        }

        $cvPath = $data['cv_path'] ?? null;
        $cvOriginalName = $data['cv_original_name'] ?? null;

        // If no custom CV is provided for this application, fallback to seeker's profile default CV
        if (! $cvPath) {
            $userProfile = DB::table('profiles')->where('user_id', $userId)->first();
            if ($userProfile && $userProfile->cv_path) {
                $cvPath = $userProfile->cv_path;
                $cvOriginalName = $userProfile->cv_original_name;
            }
        }

        return Application::create([
            'job_post_id' => $job->id,
            'user_id' => $userId,
            'cover_letter' => $data['cover_letter'] ?? null,
            'cv_path' => $cvPath,
            'cv_original_name' => $cvOriginalName,
            'status' => 'pending',
        ]);
    }

    /**
     * Update an application's status (Shortlisted, Interview, Offered, Hired, Rejected).
     */
    public function updateStatus(Application $application, string $newStatus, ?string $reason = null, ?string $hrNotes = null): Application
    {
        $updateData = ['status' => $newStatus];

        if ($hrNotes !== null) {
            $updateData['hr_notes'] = $hrNotes;
        }

        // Set status-specific transition timestamps
        match ($newStatus) {
            'shortlisted' => $updateData['shortlisted_at'] = now(),
            'rejected' => [
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ],
            'hired' => $updateData['hired_at'] = now(),
            default => null,
        };

        $application->update($updateData);

        return $application->fresh();
    }

    /**
     * Withdraw an application as a job seeker.
     */
    public function withdrawApplication(Application $application, int $userId): bool
    {
        if ($application->user_id !== $userId) {
            throw new \Exception('Unauthorized action.', 403);
        }

        if (in_array($application->status, ['hired', 'rejected', 'withdrawn'])) {
            throw ValidationException::withMessages([
                'status' => ["Cannot withdraw application once it is {$application->status}."],
            ]);
        }

        return $application->update(['status' => 'withdrawn']);
    }
}