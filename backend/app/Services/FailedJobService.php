<?php

namespace App\Services;

use App\Models\FailedJob;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;

class FailedJobService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return FailedJob::query()->latest('failed_at')->paginate($perPage);
    }

    public function all(): Collection
    {
        return FailedJob::all();
    }

    public function find(string $uuid): FailedJob
    {
        return FailedJob::where('uuid', $uuid)->firstOrFail();
    }

    public function create(array $data): FailedJob
    {
        return FailedJob::create($data);
    }

    public function update(FailedJob $failedJob, array $data): FailedJob
    {
        $failedJob->update($data);

        return $failedJob->fresh();
    }

    public function delete(FailedJob $failedJob): bool
    {
        return (bool) $failedJob->delete();
    }

    public function retry(FailedJob $failedJob): bool
    {
        return Artisan::call('queue:retry', ['id' => [$failedJob->uuid]]) === 0;
    }

    public function flush(): int
    {
        return FailedJob::query()->delete();
    }
}
