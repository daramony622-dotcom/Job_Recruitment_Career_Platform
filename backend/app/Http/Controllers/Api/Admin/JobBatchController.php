<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreJobBatchRequest;
use App\Http\Requests\Admin\UpdateJobBatchRequest;
use App\Models\JobBatch;
use App\Services\JobBatchService;
use Illuminate\Http\JsonResponse;

class JobBatchController extends Controller
{
    public function __construct(private readonly JobBatchService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->paginate());
    }

    public function store(StoreJobBatchRequest $request): JsonResponse
    {
        $batch = $this->service->create($request->validated());

        return response()->json($batch, 201);
    }

    public function show(JobBatch $jobBatch): JsonResponse
    {
        return response()->json($jobBatch);
    }

    public function update(UpdateJobBatchRequest $request, JobBatch $jobBatch): JsonResponse
    {
        $jobBatch = $this->service->update($jobBatch, $request->validated());

        return response()->json($jobBatch);
    }

    public function destroy(JobBatch $jobBatch): JsonResponse
    {
        $this->service->delete($jobBatch);

        return response()->json(['message' => 'Job batch deleted.']);
    }
}