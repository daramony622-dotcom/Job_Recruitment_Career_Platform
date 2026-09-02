<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InterviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'application_id' => $this->application_id,
            'job_post_id' => $this->job_post_id,
            'applicant_id' => $this->applicant_id,
            'interviewer_id' => $this->interviewer_id,
            'interview_type' => $this->interview_type,
            'title' => $this->title,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'duration_minutes' => $this->duration_minutes,
            'location' => $this->location,
            'meeting_link' => $this->meeting_link,
            'notes_for_candidate' => $this->notes_for_candidate,
            'internal_notes' => $this->when(
                $request->user()?->hasRole('admin') || $request->user()?->hasRole('company') || $request->user()?->hasRole('hr'),
                $this->internal_notes
            ),
            'feedback' => $this->feedback,
            'result' => $this->result,
            'status' => $this->status,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'application' => new ApplicationResource($this->whenLoaded('application')),
            'job_post' => new JobPostResource($this->whenLoaded('jobPost')),
            'applicant' => new UserResource($this->whenLoaded('applicant')),
            'interviewer' => new UserResource($this->whenLoaded('interviewer')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}