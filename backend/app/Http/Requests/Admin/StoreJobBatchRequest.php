<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'string', 'max:255', 'unique:job_batches,id'],
            'name' => ['required', 'string', 'max:255'],
            'total_jobs' => ['required', 'integer', 'min:0'],
            'pending_jobs' => ['required', 'integer', 'min:0'],
            'failed_jobs' => ['required', 'integer', 'min:0'],
            'failed_job_ids' => ['required', 'string'],
            'options' => ['nullable', 'string'],
            'cancelled_at' => ['nullable', 'integer'],
            'created_at' => ['required', 'integer'],
            'finished_at' => ['nullable', 'integer'],
        ];
    }
}