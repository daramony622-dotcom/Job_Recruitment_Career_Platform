<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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
            'user_id' => $this->user_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo' => $this->logo,
            'cover_image' => $this->cover_image,
            'website' => $this->website,
            'email' => $this->email,
            'phone' => $this->phone,
            'description' => $this->description,
            'industry' => $this->industry,
            'company_size' => $this->company_size,
            'founded_year' => $this->founded_year,
            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,
            'status' => $this->status,
            'is_verified' => (bool) $this->is_verified,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'open_jobs_count' => (int) ($this->open_jobs_count ?? 0),
            'jobs' => JobPostResource::collection($this->whenLoaded('jobs')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
