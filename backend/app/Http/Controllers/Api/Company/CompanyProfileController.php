<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Company;

class CompanyProfileController extends Controller
{
    private const PROFILE_NOT_FOUND = 'Company profile not found.';

    public function __construct(private readonly CompanyService $companyService)
    {
        
    }

    // GET /hr/profile
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (! $company) {
            $company = $user->assignedCompanies()->orderBy('name')->first()
                ?? $user->ownedCompany()->first();

            if ($company) {
                $user->update(['company_id' => $company->id]);
            }
        }

        if (!$company) {
            return response()->json(['message' => self::PROFILE_NOT_FOUND], 404);
        }

        return response()->json(['data' => $company]);
    }

    public function available(Request $request): JsonResponse
    {
        $user = $request->user();
        $companies = Company::query()
            ->where(function ($query) use ($user) {
                $query->whereHas('assignedManagers', fn ($assigned) => $assigned->whereKey($user->id))
                    ->orWhere('user_id', $user->id);
            })
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $companies]);
    }

    public function switch(Request $request): JsonResponse
    {
        $validated = $request->validate(['company_id' => ['required', 'integer', 'exists:companies,id']]);
        $user = $request->user();
        $company = Company::query()
            ->whereKey($validated['company_id'])
            ->where(function ($query) use ($user) {
                $query->whereHas('assignedManagers', fn ($assigned) => $assigned->whereKey($user->id))
                    ->orWhere('user_id', $user->id);
            })
            ->first();

        if (! $company) {
            return response()->json(['message' => 'This company is not assigned to your HR account.'], 403);
        }

        $user->update(['company_id' => $company->id]);

        return response()->json(['data' => $company, 'message' => 'Company switched successfully.']);
    }

    // POST /hr/profile
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        abort_if($request->user()->isHr(), 403, 'Only an administrator can assign a company to an HR account.');

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
            return response()->json(['message' => self::PROFILE_NOT_FOUND], 404);
        }

        $updated = $this->companyService->update($request->validated(), $company);

        return response()->json([
            'message' => 'Company profile updated successfully.',
            'data'    => $updated,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        abort_if($request->user()->isHr(), 403, 'HR accounts cannot delete company profiles.');

        $company = $request->user()->company;

        if (!$company) {
            return response()->json(['message' => self::PROFILE_NOT_FOUND], 404);
        }

        $company->delete();

        return response()->json(['message' => 'Company profile deleted successfully.']);
    }
}
