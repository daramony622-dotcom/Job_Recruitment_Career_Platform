<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Role is already enforced by RoleMiddleware ('role:admin') on the route.
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Resolve the category ID from the route parameter.
        $id = $this->route('job_category');

        return [
            'parent_id'   => ['sometimes', 'nullable', 'exists:job_categories,id'],
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'slug'        => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique('job_categories', 'slug')->ignore($id),
                'regex:/^[a-z0-9\-]+$/',
            ],
            'icon'        => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'sort_order'  => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex'       => 'The slug may only contain lowercase letters, numbers, and hyphens.',
            'slug.unique'      => 'That slug is already taken by another category.',
            'parent_id.exists' => 'The selected parent category does not exist.',
        ];
    }
}
