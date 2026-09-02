<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInterviewRequest extends FormRequest
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
            'application_id' => ['required', 'exists:applications,id'],
            'job_post_id' => ['required', 'exists:job_posts,id'],
            'applicant_id' => ['required', 'exists:users,id'],
            'interviewer_id' => ['nullable', 'exists:users,id'],
            'interview_type' => ['nullable', 'in:phone,video,onsite,technical,panel'],
            'title' => ['nullable', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:480'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_link' => ['nullable', 'url', 'max:255'],
            'notes_for_candidate' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
        ];
    }
}