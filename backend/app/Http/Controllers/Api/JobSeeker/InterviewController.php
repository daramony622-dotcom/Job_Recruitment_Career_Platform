<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInterviewRequest;
use App\Http\Requests\UpdateInterviewRequest;
use App\Http\Resources\InterviewResource;
use App\Models\Interview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InterviewController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Interview::class, 'interview');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $user = request()->user();

        $query = Interview::query()->with(['application', 'jobPost', 'applicant', 'interviewer']);

        if ($user->hasRole('admin')) {
            // Admin can see all interviews
        } elseif ($user->hasRole('company') || $user->hasRole('hr')) {
            // Company/HR can see interviews where they are the interviewer
            $query->where('interviewer_id', $user->id);
        } else {
            // Job Seekers can see interviews where they are the applicant
            $query->where('applicant_id', $user->id);
        }

        $interviews = $query->latest('scheduled_at')->get();

        return InterviewResource::collection($interviews);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInterviewRequest $request): InterviewResource
    {
        $data = $request->validated();

        if (!isset($data['interviewer_id']) && (request()->user()->hasRole('company') || request()->user()->hasRole('hr'))) {
            $data['interviewer_id'] = request()->user()->id;
        }

        $interview = Interview::create($data);
        $interview->load(['application', 'jobPost', 'applicant', 'interviewer']);

        return new InterviewResource($interview);
    }

    /**
     * Display the specified resource.
     */
    public function show(Interview $interview): InterviewResource
    {
        $interview->load(['application', 'jobPost', 'applicant', 'interviewer']);

        return new InterviewResource($interview);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInterviewRequest $request, Interview $interview): InterviewResource
    {
        $interview->update($request->validated());
        $interview->load(['application', 'jobPost', 'applicant', 'interviewer']);

        return new InterviewResource($interview);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Interview $interview): JsonResponse
    {
        $interview->delete();

        return response()->json([
            'message' => 'Interview deleted successfully.'
        ]);
    }
}