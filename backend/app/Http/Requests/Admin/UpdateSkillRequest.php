<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $skillId = $this->route('skill')->id ?? $this->route('skill');

        return [
            'name'        => ['sometimes', 'required', 'string', 'max:255', Rule::unique('skills', 'name')->ignore($skillId)],
            'slug'        => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/', Rule::unique('skills', 'slug')->ignore($skillId)],
            'category'    => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active'   => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, and hyphens.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => $this->boolean('is_active'),
            ]);
        }
    }
}