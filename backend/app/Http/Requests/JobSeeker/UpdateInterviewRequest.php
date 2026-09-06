<?php

namespace App\Http\Requests\JobSeeker;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInterviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'interview_type'      => ['sometimes', 'in:phone,video,onsite,technical,panel'],
            'title'               => ['nullable', 'string', 'max:255'],
            'scheduled_at'        => ['sometimes', 'required', 'date'],
            'duration_minutes'    => ['nullable', 'integer', 'min:15', 'max:480'],
            'location'            => ['nullable', 'string', 'max:255'],
            'meeting_link'        => ['nullable', 'url', 'max:255'],
            'notes_for_candidate' => ['nullable', 'string'],
            'internal_notes'      => ['nullable', 'string'],
            'feedback'            => ['nullable', 'string'],
            'result'              => ['nullable', 'in:pending,passed,failed,no_show,rescheduled'],
            'status'              => ['nullable', 'in:scheduled,confirmed,cancelled,completed,rescheduled'],
        ];
    }
}