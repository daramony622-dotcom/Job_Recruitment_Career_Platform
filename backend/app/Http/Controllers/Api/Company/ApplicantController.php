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
        $company = $request->user()->company;

        if (!$company) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User is not associated with any company.'
            ], 400);
        }

        $applications = $this->applicationService->listForCompany(
            $company->id,
            $request->query('status')
        );

        return response()->json([
            'status' => 'success',
            'data'   => $applications
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
            'data'   => $application->load(['jobPost', 'jobSeeker.profile', 'interviews'])
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