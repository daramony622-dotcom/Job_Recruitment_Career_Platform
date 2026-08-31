<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFailedJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'connection' => ['sometimes', 'string', 'max:255'],
            'queue' => ['sometimes', 'string', 'max:255'],
            'payload' => ['sometimes', 'string'],
            'exception' => ['sometimes', 'string'],
        ];
    }
}
