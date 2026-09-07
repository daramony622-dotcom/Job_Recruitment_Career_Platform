<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobSeeker\StoreInterviewRequest;
use App\Http\Requests\JobSeeker\UpdateInterviewRequest;
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
     * Display a listing of interviews platform-wide.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Interview::class);

        $interviews = $this->interviewService->listInterviews(
            $request->user(),
            $request->only(['status', 'interview_type', 'applicant_id', 'job_post_id']),
            $request->integer('per_page', 15)
        );

        return InterviewResource::collection($interviews);
    }

    /**
     * Schedule an interview as an administrator.
     */
    public function store(StoreInterviewRequest $request): JsonResponse
    {
        $this->authorize('create', Interview::class);

        $interview = $this->interviewService->scheduleInterview(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Interview scheduled successfully.',
            'data' => new InterviewResource($interview),
        ], 201);
    }

    /**
     * Display the specified interview.
     */
    public function show(Interview $interview): JsonResponse
    {
        $this->authorize('view', $interview);

        return response()->json([
            'data' => new InterviewResource($interview->load(['application', 'job', 'applicant', 'interviewer'])),
        ]);
    }

    /**
     * Update an interview as an administrator.
     */
    public function update(UpdateInterviewRequest $request, Interview $interview): JsonResponse
    {
        $this->authorize('update', $interview);

        $updated = $this->interviewService->updateInterview($interview, $request->validated());

        return response()->json([
            'message' => 'Interview updated successfully.',
            'data' => new InterviewResource($updated),
        ]);
    }

    /**
     * Cancel an interview as an administrator.
     */
    public function cancel(Request $request, Interview $interview): JsonResponse
    {
        $this->authorize('update', $interview);

        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $cancelled = $this->interviewService->cancelInterview($interview, $request->input('reason'));

        return response()->json([
            'message' => 'Interview cancelled successfully.',
            'data' => new InterviewResource($cancelled),
        ]);
    }

    /**
     * Delete an interview as an administrator.
     */
    public function destroy(Interview $interview): JsonResponse
    {
        $this->authorize('delete', $interview);

        $this->interviewService->deleteInterview($interview);

        return response()->json([
            'message' => 'Interview deleted successfully.',
        ]);
    }
}
