<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Role is already enforced by RoleMiddleware ('role:admin') on the route.
        // No extra check needed here.
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        return [
            'parent_id'   => ['nullable', 'exists:job_categories,id'],
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:job_categories,slug', 'regex:/^[a-z0-9\-]+$/'],
            'icon'        => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex'         => 'The slug may only contain lowercase letters, numbers, and hyphens.',
            'parent_id.exists'   => 'The selected parent category does not exist.',
        ];
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active'  => $this->boolean('is_active', true),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}