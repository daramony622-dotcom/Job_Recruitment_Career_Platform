<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Interview;
use App\Models\User;
use App\Notifications\InterviewScheduled;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InterviewService
{
    /**
     * Get paginated interviews filtered by user role and query parameters.
     */
    public function listInterviews(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Interview::query()->with([
            'application.jobPost',
            'job',
            'applicant',
            'interviewer',
        ]);

        // Authorization scoping based on user role
        if ($user->isAdmin()) {
            // Admin can see all interviews.
        } elseif ($user->isHr() || $user->hasRole('company') || $user->hasRole('hr')) {
            $companyId = $user->company?->id;
            $query->where(function ($q) use ($user, $companyId) {
                $q->where('interviewer_id', $user->id);

                if ($companyId) {
                    $q->orWhereHas('job', function ($jobQuery) use ($companyId) {
                        $jobQuery->where('company_id', $companyId);
                    });
                }
            });
        } elseif ($user->isUser() || $user->hasRole('user') || $user->hasRole('job_seeker')) {
            $query->where('applicant_id', $user->id);
        }

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['interview_type'])) {
            $query->where('interview_type', $filters['interview_type']);
        }

        if (!empty($filters['applicant_id'])) {
            $query->where('applicant_id', $filters['applicant_id']);
        }

        if (!empty($filters['job_post_id'])) {
            $query->where('job_post_id', $filters['job_post_id']);
        }

        return $query->latest('scheduled_at')->paginate($perPage);
    }

    /**
     * Schedule a new interview.
     */
    public function scheduleInterview(array $data, User $creator): Interview
    {
        $interview = DB::transaction(function () use ($data, $creator) {
            $application = Application::with(['jobPost', 'jobSeeker'])->findOrFail($data['application_id']);

            if (! $creator->isAdmin()) {
                $companyId = $creator->company?->id;

                if (! $companyId || $application->jobPost->company_id !== $companyId) {
                    throw ValidationException::withMessages([
                        'application_id' => 'You can only schedule interviews for applications to your company jobs.',
                    ]);
                }
            }

            // These relationships are derived from the application and creator.
            $data['job_post_id'] = $application->job_post_id;
            $data['applicant_id'] = $application->user_id;
            $data['interviewer_id'] = $creator->id;
            $data['status'] = $data['status'] ?? 'scheduled';
            $data['result'] = $data['result'] ?? 'pending';

            $interview = Interview::create($data);

            // Keep the application lifecycle status aligned with the scheduled interview.
            if ($application->status !== 'interview') {
                $application->update(['status' => 'interview']);
            }

            return $interview->load(['application', 'job', 'applicant', 'interviewer']);
        });

        // Dispatch only after the interview is committed so queued notifications
        // can safely serialize and reload the interview model.
        if ($interview->applicant) {
            $interview->applicant->notify(new InterviewScheduled($interview));
        }

        return $interview;
    }

    /**
     * Update existing interview details.
     */
    public function updateInterview(Interview $interview, array $data): Interview
    {
        return DB::transaction(function () use ($interview, $data) {
            $interview->update($data);

            return $interview->fresh(['application', 'job', 'applicant', 'interviewer']);
        });
    }

    /**
     * Cancel an interview.
     */
    public function cancelInterview(Interview $interview, ?string $reason = null): Interview
    {
        return DB::transaction(function () use ($interview, $reason) {
            $updateData = [
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ];

            if ($reason) {
                $updateData['internal_notes'] = ($interview->internal_notes ? $interview->internal_notes . "\n" : '') . "Cancellation Reason: " . $reason;
            }

            $interview->update($updateData);

            return $interview->fresh(['application', 'job', 'applicant', 'interviewer']);
        });
    }

    /**
     * Delete an interview.
     */
    public function deleteInterview(Interview $interview): bool
    {
        return (bool) $interview->delete();
    }
}
