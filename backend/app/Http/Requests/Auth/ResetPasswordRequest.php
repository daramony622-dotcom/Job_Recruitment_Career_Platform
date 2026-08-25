<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|lowercase',
            'code' => 'required|digits:6',
            'password' => 'required|min:8|confirmed',
        ];
    }
}
