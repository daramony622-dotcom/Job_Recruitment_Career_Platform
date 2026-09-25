<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Http\Requests\Application\UpdateApplicationStatusRequest;
use App\Services\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function __construct(private ApplicationService $applicationService) {}

    /**
     * View all applications platform-wide.
     */
    public function index(Request $request): JsonResponse
    {
        $applications = $this->applicationService->listAll(
            $request->query('status')
        );

        return response()->json([
            'status' => 'success',
            'data'   => $applications
        ]);
    }

    /**
     * View a single application.
     */
    public function show(Application $application): JsonResponse
    {
        $this->authorize('view', $application);

        return response()->json([
            'status' => 'success',
            'data'   => $application->load(['jobPost.company', 'jobSeeker.profile', 'jobSeeker.cvs', 'jobSeeker.educations', 'jobSeeker.experiences', 'jobSeeker.skills', 'interviews'])
        ]);
    }

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
            'data'    => $updated,
        ]);
    }
}
