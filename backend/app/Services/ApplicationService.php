<?php

namespace App\Services;

use App\Models\Application;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    public function apply(User $jobSeeker, array $data, ?UploadedFile $cv): Application
    {
        // Check if user has already applied for this job
        $existing = Application::where('job_post_id', $data['job_post_id'])
            ->where('user_id', $jobSeeker->id)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'job_post_id' => 'You have already applied for this job post.',
            ]);
        }

        return DB::transaction(function () use ($jobSeeker, $data, $cv) {
            $cvPath = null;
            $cvOriginalName = null;

            if ($cv) {
                $cvPath = $cv->store('cvs', 'private');
                $cvOriginalName = $cv->getClientOriginalName();
            }

            return Application::create([
                'job_post_id' => $data['job_post_id'],
                'user_id'     => $jobSeeker->id,
                'cover_letter'=> $data['cover_letter'] ?? null,
                'cv_path'     => $cvPath,
                'cv_original_name' => $cvOriginalName,
                'status'      => 'pending',
            ]);
        });
    }

    public function listForJobSeeker(User $jobSeeker, ?string $status = null)
    {
        return Application::with(['jobPost.company'])
            ->forUser($jobSeeker->id)
            ->when($status, fn ($q) => $q->status($status))
            ->latest()
            ->paginate(15);
    }

    public function listForCompany(int $companyId, ?string $status = null)
    {
        return Application::with(['jobPost', 'jobSeeker.profile'])
            ->forCompany($companyId)
            ->when($status, fn ($q) => $q->status($status))
            ->latest()
            ->paginate(15);
    }

    public function listAll(?string $status = null)
    {
        return Application::with(['jobPost.company', 'jobSeeker.profile'])
            ->when($status, fn ($q) => $q->status($status))
            ->latest()
            ->paginate(15);
    }

    public function updateStatus(Application $application, string $status, ?string $hrNotes = null, ?string $rejectionReason = null): Application
    {
        $application->status = $status;

        if ($hrNotes !== null) {
            $application->hr_notes = $hrNotes;
        }

        if ($status === 'shortlisted') {
            $application->shortlisted_at = now();
        } elseif ($status === 'rejected') {
            $application->rejected_at = now();
            if ($rejectionReason !== null) {
                $application->rejection_reason = $rejectionReason;
            }
        } elseif ($status === 'hired') {
            $application->hired_at = now();
        }

        $application->save();

        return $application->fresh(['jobPost', 'jobSeeker.profile']);
    }

    public function shortlist(Application $application, ?string $hrNotes = null): Application
    {
        return $this->updateStatus($application, 'shortlisted', $hrNotes);
    }

    public function reject(Application $application, ?string $rejectionReason = null, ?string $hrNotes = null): Application
    {
        return $this->updateStatus($application, 'rejected', $hrNotes, $rejectionReason);
    }

    public function withdraw(Application $application): Application
    {
        if ($application->status === 'withdrawn') {
            throw ValidationException::withMessages([
                'status' => 'Application is already withdrawn.',
            ]);
        }

        $application->update(['status' => 'withdrawn']);

        return $application;
    }
}