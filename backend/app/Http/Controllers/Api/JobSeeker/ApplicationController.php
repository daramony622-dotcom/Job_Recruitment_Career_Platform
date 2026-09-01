<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use App\Http\Requests\Application\StoreApplicationRequest;
use App\Models\Application;
use App\Services\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function __construct(private ApplicationService $applicationService) {}

    /**
     * Apply to a job post.
     */
    public function store(StoreApplicationRequest $request): JsonResponse
    {
        $application = $this->applicationService->apply(
            $request->user(),
            $request->validated(),
            $request->file('cv')
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Application submitted successfully.',
            'data'    => $application->load('jobPost.company')
        ], 201);
    }

    /**
     * View own application history.
     */
    public function index(Request $request): JsonResponse
    {
        $applications = $this->applicationService->listForJobSeeker(
            $request->user(),
            $request->query('status')
        );

        return response()->json([
            'status' => 'success',
            'data'   => $applications
        ]);
    }

    /**
     * View a single application detail.
     */
    public function show(Application $application): JsonResponse
    {
        $this->authorize('view', $application);

        return response()->json([
            'status' => 'success',
            'data'   => $application->load(['jobPost.company', 'interviews'])
        ]);
    }

    /**
     * Withdraw an application.
     */
    public function withdraw(Application $application): JsonResponse
    {
        $this->authorize('withdraw', $application);

        $withdrawn = $this->applicationService->withdraw($application);

        return response()->json([
            'status'  => 'success',
            'message' => 'Application withdrawn successfully.',
            'data'    => $withdrawn
        ]);
    }
}