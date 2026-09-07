<?php

namespace App\Http\Requests\JobSeeker;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isUser() ?? false;
    }

    public function rules(): array
    {
        return [
            'job_post_id' => [
                'required',
                'integer',
                Rule::exists('job_posts', 'id')->where(function ($query) {
                    $query->where('status', 'published')
                        ->whereNull('deleted_at')
                        ->where(function ($query) {
                            $query->whereNull('deadline')
                                ->orWhereDate('deadline', '>=', today());
                        });
                }),
            ],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'job_post_id.required' => 'Please select a job post.',
            'job_post_id.exists' => 'This job post is no longer available for applications.',
            'cover_letter.max' => 'The cover letter must not exceed 5000 characters.',
            'cv.mimes' => 'The CV must be a PDF, DOC, or DOCX file.',
            'cv.max' => 'The CV must not exceed 5 MB.',
        ];
    }
}
