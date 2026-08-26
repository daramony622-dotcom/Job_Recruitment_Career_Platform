<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function index(Request $request): JsonResponse
    {
        // Admin can view all companies with filters (status, search, etc.)
        $query = Company::with('user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $companies = $query->paginate(15);

        return response()->json([
            'data' => $companies
        ]);
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        // Admin can create a company on behalf of any user
        $user = \App\Models\User::findOrFail($request->input('user_id'));
        $company = $this->companyService->store($request->validated(), $user);

        return response()->json([
            'message' => 'Company created successfully by admin.',
            'data' => $company
        ], 201);
    }

    public function show(Company $company): JsonResponse
    {
        return response()->json([
            'data' => $company->load('user')
        ]);
    }

    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        $updatedCompany = $this->companyService->update($request->validated(), $company);

        return response()->json([
            'message' => 'Company updated successfully by admin.',
            'data' => $updatedCompany
        ]);
    }

    public function updateStatus(Request $request, Company $company): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,suspended'],
        ]);

        $company->update([
            'status' => $request->status,
            'is_verified' => $request->status === 'approved',
            'verified_at' => $request->status === 'approved' ? now() : null,
        ]);

        return response()->json([
            'message' => 'Company status updated successfully.',
            'data' => $company
        ]);
    }

    public function destroy(Company $company): JsonResponse
    {
        $company->delete();

        return response()->json([
            'message' => 'Company deleted successfully.'
        ]);
    }
}