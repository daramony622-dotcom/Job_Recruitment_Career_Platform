<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = $this->user();

        return $user && $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'company_id' => 'sometimes|exists:companies,id',
            'category_id' => 'sometimes|exists:job_categories,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'job_type' => 'sometimes|in:full_time,part_time,contract,internship,freelance,remote',
            'work_mode' => 'sometimes|in:onsite,remote,hybrid',
            'experience_level' => 'nullable|in:entry,junior,mid,senior,lead,executive',
            'location' => 'sometimes|string|max:255',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'salary_currency' => 'nullable|string|max:10|in:USD,EUR,GBP,KHR',
            'salary_period' => 'nullable|in:hourly,daily,monthly,yearly',
            'is_salary_visible' => 'nullable|boolean',
            'vacancies' => 'nullable|integer|min:1|max:100',
            'deadline' => 'nullable|date',
            'status' => 'sometimes|in:draft,published,closed,suspended',
            'is_featured' => 'nullable|boolean',

            // Skills array (optional for updates)
            'skills' => 'sometimes|array|min:1',
            'skills.*.id' => 'required|exists:skills,id',
            'skills.*.level' => 'nullable|in:beginner,intermediate,advanced,expert',
            'skills.*.is_required' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.exists' => 'The selected company does not exist.',
            'category_id.exists' => 'The selected category does not exist.',
            'skills.*.id.exists' => 'One or more selected skills are invalid.',
            'salary_max.gte' => 'Maximum salary must be greater than or equal to minimum salary.',
        ];
    }
}