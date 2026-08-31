<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateJobPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Allow admin, company, and hr roles.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = $this->user();

        return $user && ($user->isAdmin() || $user->isHr());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Note: company_id is NOT included – it's auto-set in prepareForValidation
            'category_id' => 'sometimes|exists:job_categories,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:5000',
            'requirements' => 'nullable|string|max:5000',
            'benefits' => 'nullable|string|max:5000',
            'job_type' => 'sometimes|in:full_time,part_time,contract,internship,freelance,remote',
            'work_mode' => 'sometimes|in:onsite,remote,hybrid',
            'experience_level' => 'nullable|in:entry,junior,mid,senior,lead,executive',
            'location' => 'sometimes|string|max:255',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'salary_min' => 'nullable|numeric|min:0|max:99999999.99',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min|max:99999999.99',
            'salary_currency' => 'nullable|string|max:10|in:USD,EUR,GBP,KHR',
            'salary_period' => 'nullable|in:hourly,daily,monthly,yearly',
            'is_salary_visible' => 'nullable|boolean',
            'vacancies' => 'nullable|integer|min:1|max:100',
            'deadline' => 'nullable|date|after:today', // You can allow past dates if needed; adjust as necessary
            'status' => 'sometimes|in:draft,published,closed,suspended',
            'is_featured' => 'nullable|boolean',

            // Skills array (optional for updates)
            'skills' => 'sometimes|array|min:1',
            'skills.*.id' => 'required|exists:skills,id',
            'skills.*.level' => 'nullable|in:beginner,intermediate,advanced,expert',
            'skills.*.is_required' => 'nullable|boolean',
        ];
    }

    /**
     * Prepare the data – ensure company_id is always the user's company.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Prevent changing company – always set to the authenticated user's company
            'company_id' => Auth::user()->company?->id,
            // Set defaults if not provided
            'is_salary_visible' => $this->input('is_salary_visible', true),
            'is_featured' => $this->input('is_featured', false),
        ]);
    }

    /**
     * Get custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'category_id.exists' => 'The selected category does not exist.',
            'title.max' => 'The title must not exceed 255 characters.',
            'description.max' => 'The description must not exceed 5000 characters.',
            'job_type.in' => 'Invalid job type selected.',
            'work_mode.in' => 'Invalid work mode selected.',
            'salary_min.min' => 'Minimum salary must be at least 0.',
            'salary_max.gte' => 'Maximum salary must be greater than or equal to minimum salary.',
            'salary_max.max' => 'Salary amount is too high.',
            'vacancies.min' => 'Vacancies must be at least 1.',
            'vacancies.max' => 'Vacancies cannot exceed 100.',
            'deadline.after' => 'The deadline must be a future date.',
            'status.in' => 'Invalid status selected.',
            'skills.*.id.exists' => 'One or more selected skills are invalid.',
            'skills.*.level.in' => 'Invalid skill level selected.',
        ];
    }

    /**
     * Get custom attribute names for error messages.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'job category',
            'title' => 'job title',
            'description' => 'job description',
            'job_type' => 'job type',
            'work_mode' => 'work mode',
            'experience_level' => 'experience level',
            'salary_min' => 'minimum salary',
            'salary_max' => 'maximum salary',
            'deadline' => 'application deadline',
            'skills' => 'skills list',
            'skills.*.level' => 'skill level',
            'skills.*.is_required' => 'skill requirement',
        ];
    }
}