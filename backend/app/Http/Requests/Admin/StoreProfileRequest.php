<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id', 'unique:profiles,user_id'],
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'nationality' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // 5MB Max
            'availability' => ['nullable', Rule::in(['immediately', 'within_1_month', 'within_3_months', 'not_available'])],
            'expected_salary_min' => ['nullable', 'numeric', 'min:0'],
            'expected_salary_max' => ['nullable', 'numeric', 'gte:expected_salary_min'],
            'salary_currency' => ['nullable', 'string', 'max:10'],
            'is_open_to_work' => ['nullable', 'boolean'],
            'is_profile_visible' => ['nullable', 'boolean'],
        ];
    }
}