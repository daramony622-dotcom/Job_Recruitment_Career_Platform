<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreJobRequest;
use App\Http\Requests\Admin\UpdateJobRequest;
use App\Models\Job;
use App\Services\JobService;
use Illuminate\Http\JsonResponse;

class JobController extends Controller
{
    public function __construct(private readonly JobService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->paginate());
    }

    public function store(StoreJobRequest $request): JsonResponse
    {
        $job = $this->service->create($request->validated());
        if (!$job){
            return response()->json([
                "status"=> false,
                "message"=> "Create job not success",
            ],404);
        }
        return response()->json([
            "status"=> true,
            "message"=> "create job successful!",
            "data"=>$job
        ]);
    }

    public function show(Job $job): JsonResponse
    {
        $job = $this->service->find($job->id);
        if (!$job){
            return response()->json([
                "status"=> false,
                "message"=> "Job not found!",
                ],404);
        }
        return response()->json([
            "status"=> true,
            "message"=> "Found result!",
            "data"=>$job
        ]);
    }

    public function update(UpdateJobRequest $request, Job $job): JsonResponse
    {
        $job = $this->service->update($job, $request->validated());
        if (!$job){
            return response()->json([
                "status"=> false,
                "message"=> "Job can't update!",
            ],404);
        }
        return response()->json([
            "status"=> true,
            "message"=> "Job update successful!",
            "data"=>$job
        ]);

    }

    public function destroy(Job $job): JsonResponse
    {
        $job = $this->service->find($job->id);
        if (!$job){
            return response()->json([
            "status"=> false,
            "message"=> "Job not found!",
            ],404);
        }
        $job->delete();
        return response()->json([
            "status"=> true,
            "message"=> "Job delete successful!",
            "data"=>$job
            ],200);
    }
}