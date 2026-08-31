<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CompanyController extends Controller
{
    // Constructor Property Promotion (PHP 8.0+)
    public function __construct(
        protected CompanyService $companyService
    ) {}

    /**
     * Display a listing of companies with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Company::class);

        $query = Company::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $companies = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => $companies
        ]);
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        Gate::authorize('create', Company::class);

        $validated = $request->validated();
        $user = User::findOrFail($validated['user_id']);
        $company = $this->companyService->store($validated, $user);

        return response()->json([
            'message' => 'Company created successfully by admin.',
            'data'    => $company,
        ], 201);
    }

    /**
     * Display the specified company.
     */
    public function show(Company $company): JsonResponse
    {
        Gate::authorize('view', $company);

        return response()->json([
            'data' => $company->load('user')
        ]);
    }

    /**
     * Update the specified company.
     */
    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        Gate::authorize('update', $company);

        $updatedCompany = $this->companyService->update($request->validated(), $company);

        return response()->json([
            'message' => 'Company updated successfully by admin.',
            'data'    => $updatedCompany
        ]);
    }

    /**
     * Update the status of a specific company.
     */
    public function updateStatus(Request $request, Company $company): JsonResponse
    {
        Gate::authorize('update', $company);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,approved,rejected,suspended'],
        ]);

        $company->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Company status updated successfully.',
            'data'    => $company,
        ]);
    }

    /**
     * Remove the specified company from storage.
     */
    public function destroy(Company $company): JsonResponse
    {
        Gate::authorize('delete', $company);

        $company->delete();

        return response()->json([
            'message' => 'Company deleted successfully.'
        ]);
    }
}