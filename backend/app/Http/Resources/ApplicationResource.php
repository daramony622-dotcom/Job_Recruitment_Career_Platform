<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
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
            'job_post_id' => $this->job_post_id,
            'user_id' => $this->user_id,
            'cover_letter' => $this->cover_letter,
            'cv_path' => $this->cv_path,
            'cv_original_name' => $this->cv_original_name,
            'status' => $this->status,
            'hr_notes' => $this->hr_notes,
            'rejection_reason' => $this->rejection_reason,
            'shortlisted_at' => $this->shortlisted_at?->toIso8601String(),
            'rejected_at' => $this->rejected_at?->toIso8601String(),
            'hired_at' => $this->hired_at?->toIso8601String(),
            'job_post' => new JobPostResource($this->whenLoaded('jobPost')),
            'job_seeker' => new UserResource($this->whenLoaded('jobSeeker')),
            'user' => new UserResource($this->whenLoaded('user')),
            'interviews' => InterviewResource::collection($this->whenLoaded('interviews')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
