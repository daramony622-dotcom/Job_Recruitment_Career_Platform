<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = $this->user();

        return $user && $user->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Job Post fields
            'company_id' => 'required|exists:companies,id',
            'category_id' => 'required|exists:job_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'job_type' => 'required|in:full_time,part_time,contract,internship,freelance,remote',
            'work_mode' => 'required|in:onsite,remote,hybrid',
            'experience_level' => 'nullable|in:entry,junior,mid,senior,lead,executive',
            'location' => 'required|string|max:255',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'salary_currency' => 'nullable|string|max:10|in:USD,EUR,GBP,KHR',
            'salary_period' => 'nullable|in:hourly,daily,monthly,yearly',
            'is_salary_visible' => 'nullable|boolean',
            'vacancies' => 'nullable|integer|min:1|max:100',
            'deadline' => 'nullable|date|after:today',
            'status' => 'required|in:draft,published,closed,suspended',
            'is_featured' => 'nullable|boolean',

            // Skills array — optional for drafts, recommended for published
            'skills'           => 'nullable|array',
            'skills.*.id'      => 'required|exists:skills,id',
            'skills.*.level'   => 'nullable|in:beginner,intermediate,advanced,expert',
            'skills.*.is_required' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'company_id.required' => 'A company must be selected.',
            'company_id.exists' => 'The selected company does not exist.',
            'category_id.required' => 'A category must be selected.',
            'category_id.exists' => 'The selected category does not exist.',
            'skills.required' => 'At least one skill is required.',
            'skills.*.id.exists' => 'One or more selected skills are invalid.',
            'salary_max.gte' => 'Maximum salary must be greater than or equal to minimum salary.',
            'deadline.after' => 'The deadline must be a future date.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_salary_visible' => $this->input('is_salary_visible', true),
            'is_featured' => $this->input('is_featured', false),
            'vacancies' => $this->input('vacancies', 1),
            'salary_currency' => $this->input('salary_currency', 'USD'),
            'salary_period' => $this->input('salary_period', 'monthly'),
        ]);
    }
}