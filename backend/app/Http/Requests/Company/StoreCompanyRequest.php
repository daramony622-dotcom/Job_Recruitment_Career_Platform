<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // user_id is only required when admin creates a company on behalf of a user
            'user_id'      => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'name'         => ['required', 'string', 'max:255'],
            'website'      => ['nullable', 'url', 'max:255'],
            'email'        => ['nullable', 'email', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'description'  => ['nullable', 'string'],
            'industry'     => ['nullable', 'string', 'max:255'],
            'company_size' => ['nullable', 'in:1-10,11-50,51-200,201-500,501-1000,1000+'],
            'founded_year' => ['nullable', 'digits:4', 'integer', 'min:1800', 'max:' . date('Y')],
            'country'      => ['nullable', 'string', 'max:255'],
            'city'         => ['nullable', 'string', 'max:255'],
            'address'      => ['nullable', 'string', 'max:500'],
            'logo'         => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'cover_image'  => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ];
    }
}
