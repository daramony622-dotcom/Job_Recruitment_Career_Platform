<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            'avatar' => $this->avatar,
            'headline' => $this->headline,
            'bio' => $this->bio,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'nationality' => $this->nationality,
            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,
            'linkedin_url' => $this->linkedin_url,
            'github_url' => $this->github_url,
            'portfolio_url' => $this->portfolio_url,
            'cv_path' => $this->cv_path,
            'cv_original_name' => $this->cv_original_name,
            'cv_uploaded_at' => $this->cv_uploaded_at?->toIso8601String(),
            'availability' => $this->availability,
            'expected_salary_min' => $this->expected_salary_min,
            'expected_salary_max' => $this->expected_salary_max,
            'salary_currency' => $this->salary_currency,
            'is_open_to_work' => $this->is_open_to_work,
            'is_profile_visible' => $this->is_profile_visible,
            'profile_views' => $this->profile_views,
            'user' => $this->whenLoaded('user'),
            'educations' => $this->whenLoaded('educations'),
            'experiences' => $this->whenLoaded('experiences'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}