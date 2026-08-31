<?php

namespace App\Services;

use App\Models\Job;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class JobService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Job::query()->latest('created_at')->paginate($perPage);
    }

    public function all(): Collection
    {
        return Job::all();
    }

    public function find(int $id): Job
    {
        return Job::findOrFail($id);
    }

    public function create(array $data): Job
    {
        $data['attempts'] = $data['attempts'] ?? 0;

        return Job::create($data);
    }

    public function update(Job $job, array $data): Job
    {
        $job->update($data);

        return $job->fresh();
    }

    public function delete(Job $job): bool
    {
        return (bool) $job->delete();
    }
}