<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreJobPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            // company_id is auto-set in prepareForValidation; must resolve to a real company
            'company_id'       => 'required|exists:companies,id',

            'category_id'      => 'required|exists:job_categories,id',
            'title'            => 'required|string|max:255',
            'description'      => 'required|string|max:5000',
            'requirements'     => 'nullable|string|max:5000',
            'benefits'         => 'nullable|string|max:5000',
            'job_type'         => 'required|in:full_time,part_time,contract,internship,freelance,remote',
            'work_mode'        => 'required|in:onsite,remote,hybrid',
            'experience_level' => 'nullable|in:entry,junior,mid,senior,lead,executive',
            'location'         => 'required|string|max:255',
            'country'          => 'nullable|string|max:100',
            'city'             => 'nullable|string|max:100',
            'salary_min'       => 'nullable|numeric|min:0|max:99999999.99',
            'salary_max'       => 'nullable|numeric|min:0|gte:salary_min|max:99999999.99',
            'salary_currency'  => 'nullable|string|max:10|in:USD,EUR,GBP,KHR',
            'salary_period'    => 'nullable|in:hourly,daily,monthly,yearly',
            'is_salary_visible'=> 'nullable|boolean',
            'vacancies'        => 'nullable|integer|min:1|max:100',
            'deadline'         => 'nullable|date|after:today',
            'status'           => 'required|in:draft,published,closed,suspended',
            'is_featured'      => 'nullable|boolean',

            // Skills are optional for drafts but required for published posts
            'skills'           => 'required_if:status,published|nullable|array|min:1',
            'skills.*.id'      => 'required|exists:skills,id|distinct',
            'skills.*.level'   => 'nullable|in:beginner,intermediate,advanced,expert',
            'skills.*.is_required' => 'nullable|boolean',
        ];
    }

    /**
     * Prepare the data for validation – auto-set company_id from auth.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_id' => Auth::user()->company?->id,
            'is_salary_visible' => $this->input('is_salary_visible', true),
            'is_featured' => $this->input('is_featured', false),
            'vacancies' => $this->input('vacancies', 1),
            'salary_currency' => $this->input('salary_currency', 'USD'),
            'salary_period' => $this->input('salary_period', 'monthly'),
        ]);
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select a job category.',
            'category_id.exists' => 'The selected category does not exist.',
            'title.required' => 'Job title is required.',
            'description.required' => 'Job description is required.',
            'skills.required' => 'At least one skill is required.',
            'skills.*.id.exists' => 'One or more selected skills are invalid.',
            'salary_max.gte' => 'Maximum salary must be greater than or equal to minimum salary.',
            'deadline.after' => 'The deadline must be a future date.',
            'status.required' => 'Please select a status.',
        ];
    }
}
