<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Application\UpdateApplicationStatusRequest;
use App\Models\Application;
use App\Services\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicantController extends Controller
{
    public function __construct(private ApplicationService $applicationService) {}

    /**
     * View applicants for jobs posted by this company.
     */

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $companyId = $request->query('company_id');
        $status = $request->query('status');

        if ($companyId) {
            $applications = $this->applicationService->listForCompany((int) $companyId, $status);
        } elseif ($user->isAdmin() || $user->isHr()) {
            $applications = $this->applicationService->listAll($status);
        } elseif ($user->company) {
            $applications = $this->applicationService->listForCompany($user->company->id, $status);
        } else {
            $applications = $this->applicationService->listAll($status);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $applications,
        ]);
    }

    /**
     * Display the specified applicant's application.
     */

    public function show(Application $application): JsonResponse
    {
        $this->authorize('view', $application);

        return response()->json([
            'status' => 'success',
            'data'   => $application->load(['jobPost.company', 'jobSeeker.profile', 'jobSeeker.cvs', 'jobSeeker.educations', 'jobSeeker.experiences', 'jobSeeker.skills', 'interviews'])
        ]);
    }

    /**
     * Shortlist an applicant.
     */
    public function shortlist(Request $request, Application $application): JsonResponse
    {
        $this->authorize('updateStatus', $application);

        $updated = $this->applicationService->shortlist(
            $application,
            $request->input('hr_notes')
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Applicant shortlisted successfully.',
            'data'    => $updated
        ]);
    }

    /**
     * Reject an applicant.
     */
    public function reject(Request $request, Application $application): JsonResponse
    {
        $this->authorize('updateStatus', $application);

        $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
            'hr_notes'         => ['nullable', 'string', 'max:2000'],
        ]);

        $updated = $this->applicationService->reject(
            $application,
            $request->input('rejection_reason'),
            $request->input('hr_notes')
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Applicant rejected.',
            'data'    => $updated
        ]);
    }

    /**
     * Update application status (shortlist, reject, interview, offered, hired).
     */
    public function updateStatus(UpdateApplicationStatusRequest $request, Application $application): JsonResponse
    {
        $this->authorize('updateStatus', $application);

        $updated = $this->applicationService->updateStatus(
            $application,
            $request->validated('status'),
            $request->validated('hr_notes'),
            $request->validated('rejection_reason')
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Application status updated successfully.',
            'data'    => $updated
        ]);
    }
}
