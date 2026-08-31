<?php

namespace App\Services;

use App\Models\JobBatch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class JobBatchService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return JobBatch::query()->latest('created_at')->paginate($perPage);
    }

    public function all(): Collection
    {
        return JobBatch::all();
    }

    public function find(string $id): JobBatch
    {
        return JobBatch::findOrFail($id);
    }

    public function create(array $data): JobBatch
    {
        return JobBatch::create($data);
    }

    public function update(JobBatch $jobBatch, array $data): JobBatch
    {
        $jobBatch->update($data);

        return $jobBatch->fresh();
    }

    public function delete(JobBatch $jobBatch): bool
    {
        return (bool) $jobBatch->delete();
    }
}