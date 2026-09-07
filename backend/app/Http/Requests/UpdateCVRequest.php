<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCVRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('is_primary')) {
            $value = strtolower(trim((string) $this->input('is_primary')));

            if (in_array($value, ['true', '1'], true)) {
                $this->merge(['is_primary' => true]);
            } elseif (in_array($value, ['false', '0'], true)) {
                $this->merge(['is_primary' => false]);
            }
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'file_path' => ['sometimes', 'file', 'mimes:pdf,doc,docx,ppt,pptx,png,jpg,jpeg', 'max:5120'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}