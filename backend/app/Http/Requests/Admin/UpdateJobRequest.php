<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'queue' => ['sometimes', 'string', 'max:255'],
            'payload' => ['sometimes', 'string'],
            'attempts' => ['sometimes', 'integer', 'min:0'],
            'reserved_at' => ['nullable', 'integer'],
            'available_at' => ['sometimes', 'integer'],
        ];
    }
}