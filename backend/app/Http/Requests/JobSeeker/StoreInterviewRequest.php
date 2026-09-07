<?php

namespace App\Http\Requests\JobSeeker;

use Illuminate\Foundation\Http\FormRequest;

class StoreInterviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_id'      => ['required', 'exists:applications,id'],
            'job_post_id'         => ['prohibited'],
            'applicant_id'        => ['prohibited'],
            'interviewer_id'      => ['prohibited'],
            'interview_type'      => ['nullable', 'in:phone,video,onsite,technical,panel'],
            'title'               => ['nullable', 'string', 'max:255'],
            'scheduled_at'        => ['required', 'date', 'after:now'],
            'duration_minutes'    => ['nullable', 'integer', 'min:15', 'max:480'],
            'location'            => ['nullable', 'string', 'max:255'],
            'meeting_link'        => ['nullable', 'url', 'max:255'],
            'notes_for_candidate' => ['nullable', 'string'],
            'internal_notes'      => ['nullable', 'string'],
        ];
    }
}
