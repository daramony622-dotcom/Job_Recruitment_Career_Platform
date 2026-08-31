<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFailedJobRequest;
use App\Http\Requests\Admin\UpdateFailedJobRequest;
use App\Models\FailedJob;
use App\Services\FailedJobService;
use Illuminate\Http\JsonResponse;

class FailedJobController extends Controller
{
    public function __construct(private readonly FailedJobService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data'   => $this->service->paginate(),
        ]);
    }

    public function store(StoreFailedJobRequest $request): JsonResponse
    {
        $job = $this->service->create($request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Failed job recorded successfully.',
            'data'    => $job,
        ], JsonResponse::HTTP_CREATED);
    }

    public function show(FailedJob $failedJob): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => 'Failed job found.',
            'data'    => $failedJob,
        ]);
    }

    public function update(UpdateFailedJobRequest $request, FailedJob $failedJob): JsonResponse
    {
        $updated = $this->service->update($failedJob, $request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Failed job updated successfully.',
            'data'    => $updated,
        ]);
    }

    public function destroy(FailedJob $failedJob): JsonResponse
    {
        $this->service->delete($failedJob);

        return response()->json([
            'status'  => true,
            'message' => 'Failed job deleted.',
        ]);
    }

    public function retry(FailedJob $failedJob): JsonResponse
    {
        if (! $this->service->retry($failedJob)) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to re-queue job.',
            ], 422);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Job re-queued for retry.',
        ]);
    }

    public function flush(): JsonResponse
    {
        $count = $this->service->flush();

        return response()->json([
            'status'  => true,
            'message' => "{$count} failed jobs deleted.",
            'count'   => $count,
        ]);
    }
}
