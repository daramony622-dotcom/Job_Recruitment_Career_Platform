<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFailedJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('uuid') || empty($this->uuid)) {
            $this->merge([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'uuid' => ['required', 'string', 'unique:failed_jobs,uuid'],
            'connection' => ['required', 'string', 'max:255'],
            'queue' => ['required', 'string', 'max:255'],
            'payload' => ['required', 'string'],
            'exception' => ['required', 'string'],
        ];
    }
}
