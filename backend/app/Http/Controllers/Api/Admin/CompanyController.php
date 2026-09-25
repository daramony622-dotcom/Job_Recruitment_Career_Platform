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

        $query = Company::with(['user', 'assignedManagers'])
            ->withCount([
                'jobs as open_jobs_count' => fn ($jobQuery) => $jobQuery->published(),
            ]);

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
        $managerIds = $validated['manager_ids'] ?? [];
        unset($validated['manager_ids']);
        $userId = $validated['user_id'] ?? $request->user()->id;
        $user = User::findOrFail($userId);
        $company = $this->companyService->store($validated, $user);
        $this->companyService->syncManagers($company, array_unique(array_merge($managerIds, [$userId])));

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

        $company->loadCount([
            'jobs as open_jobs_count' => fn ($jobQuery) => $jobQuery->published(),
        ]);

        $company->load([
            'user',
            'assignedManagers',
            'jobs' => fn ($jobQuery) => $jobQuery->published()->with('category'),
        ]);

        return response()->json([
            'data' => $company,
        ]);
    }

    /**
     * Update the specified company.
     */
    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        Gate::authorize('update', $company);

        $validated = $request->validated();
        $managerIds = $validated['manager_ids'] ?? null;
        unset($validated['manager_ids']);
        $updatedCompany = $this->companyService->update($validated, $company);

        if ($managerIds !== null) {
            $this->companyService->syncManagers($company, array_unique(array_merge($managerIds, [$company->user_id])));
        }

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