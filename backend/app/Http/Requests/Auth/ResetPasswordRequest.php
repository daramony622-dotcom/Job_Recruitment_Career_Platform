<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->email)),
            ]);
        }
        if ($this->has('code')) {
            $this->merge([
                'code' => trim((string) $this->code),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'code' => 'required|string',
            'password' => 'required|min:6',
        ];
    }
}
