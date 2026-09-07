<?php

namespace App\Http\Requests\JobSeeker;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSavedJobRequest extends FormRequest
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
            'job_post_id' => [
                'required',
                'integer',
                'exists:job_posts,id',
                Rule::unique('saved_jobs', 'job_post_id')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id);
                }),
            ],
            'notes' => ['nullable', 'string'],
        ];
    }
}