<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['sometimes', 'required', 'string', 'max:255'],
            'website'      => ['sometimes', 'nullable', 'url', 'max:255'],
            'email'        => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone'        => ['sometimes', 'nullable', 'string', 'max:20'],
            'description'  => ['sometimes', 'nullable', 'string'],
            'industry'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_size' => ['sometimes', 'nullable', 'in:1-10,11-50,51-200,201-500,501-1000,1000+'],
            'founded_year' => ['sometimes', 'nullable', 'digits:4', 'integer', 'min:1800', 'max:' . date('Y')],
            'country'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'city'         => ['sometimes', 'nullable', 'string', 'max:255'],
            'address'      => ['sometimes', 'nullable', 'string', 'max:500'],
            'logo'         => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'cover_image'  => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ];
    }
}
