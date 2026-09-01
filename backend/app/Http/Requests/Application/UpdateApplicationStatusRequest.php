<?php

namespace App\Http\Requests\Application;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->isAdmin() || $user->isHr() || in_array($user->role, ['admin', 'hr']));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                'pending', 'reviewing', 'shortlisted', 'interview',
                'offered', 'hired', 'rejected', 'withdrawn'
            ])],
            'hr_notes' => ['nullable', 'string', 'max:2000'],
            'rejection_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:1000'],
        ];
    }
}