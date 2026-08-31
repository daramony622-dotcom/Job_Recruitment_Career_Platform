<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'name'      => ['sometimes', 'string', 'max:255'],
            'email'     => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'is_active' => ['sometimes', 'boolean'],
            'profile'   => ['sometimes', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.email'       => 'Please provide a valid email address.',
            'email.unique'      => 'This email is already taken by another user.',
            'is_active.boolean' => 'The active status must be true or false.',
        ];
    }
}