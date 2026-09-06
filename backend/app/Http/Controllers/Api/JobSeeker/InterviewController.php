<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use App\Http\Resources\InterviewResource;
use App\Models\Interview;
use App\Services\InterviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InterviewController extends Controller
{
    public function __construct(private readonly InterviewService $interviewService)
    {
    }

    /**
     * Display a listing of scheduled interviews for the jobseeker.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Interview::class);

        $interviews = $this->interviewService->listInterviews(
            $request->user(),
            $request->only(['status', 'interview_type']),
            $request->integer('per_page', 15)
        );

        return InterviewResource::collection($interviews);
    }

    /**
     * Display the specified interview for the jobseeker.
     */
    public function show(Interview $interview): JsonResponse
    {
        $this->authorize('view', $interview);

        return response()->json([
            'data' => new InterviewResource($interview->load(['application', 'job', 'applicant', 'interviewer'])),
        ]);
    }
}