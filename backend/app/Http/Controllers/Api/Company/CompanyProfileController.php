<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    public function __construct(private readonly CompanyService $companyService)
    {
    }

    // GET /hr/profile
    public function show(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return response()->json(['message' => 'Company profile not found.'], 404);
        }

        return response()->json(['data' => $company]);
    }

    // POST /hr/profile
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        if ($request->user()->company) {
            return response()->json(['message' => 'Company profile already exists.'], 422);
        }

        $company = $this->companyService->store($request->validated(), $request->user());

        return response()->json([
            'message' => 'Company profile created successfully.',
            'data'    => $company,
        ], 201);
    }

    // PUT /hr/profile
    public function update(UpdateCompanyRequest $request): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return response()->json(['message' => 'Company profile not found.'], 404);
        }

        $updated = $this->companyService->update($request->validated(), $company);

        return response()->json([
            'message' => 'Company profile updated successfully.',
            'data'    => $updated,
        ]);
    }
}
