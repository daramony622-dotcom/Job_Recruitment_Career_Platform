<?php

namespace App\Http\Requests\Application;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isUser() || $this->user()?->role === 'user';
    }

    public function rules(): array
    {
        return [
            'job_post_id' => ['required', 'exists:job_posts,id'],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'cv'           => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // 5MB max
        ];
    }
}