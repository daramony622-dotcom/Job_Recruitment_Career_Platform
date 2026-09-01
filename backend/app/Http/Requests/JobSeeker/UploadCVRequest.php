<?php

namespace App\Http\Requests\JobSeeker;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadCVRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'file_path' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // Max 5MB
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}