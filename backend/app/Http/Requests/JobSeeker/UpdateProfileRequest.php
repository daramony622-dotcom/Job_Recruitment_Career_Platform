<?php

namespace App\Http\Requests\JobSeeker;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()?->id),
            ],
            'headline' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before:today'],
            'gender' => ['sometimes', 'nullable', 'in:male,female,other,prefer_not_to_say'],
            'nationality' => ['sometimes', 'nullable', 'string', 'max:255'],
            'country' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'linkedin_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'github_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'portfolio_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'availability' => ['sometimes', 'in:immediately,within_1_month,within_3_months,not_available'],
            'expected_salary_min' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'expected_salary_max' => ['sometimes', 'nullable', 'numeric', 'gte:expected_salary_min'],
            'salary_currency' => ['sometimes', 'string', 'size:3'],
            'is_open_to_work' => ['sometimes', 'boolean'],
            'is_profile_visible' => ['sometimes', 'boolean'],
        ];
    }
}
