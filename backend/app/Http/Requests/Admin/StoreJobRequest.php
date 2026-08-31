<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'queue' => ['required', 'string', 'max:255'],
            'payload' => ['required', 'string'],
            'attempts' => ['nullable', 'integer', 'min:0'],
            'reserved_at' => ['nullable', 'integer'],
            'available_at' => ['required', 'integer'],
            'created_at' => ['required', 'integer'],
        ];
    }
}
