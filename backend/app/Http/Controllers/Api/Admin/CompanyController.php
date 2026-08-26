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

class CompanyController extends Controller
{
    public function __construct(private readonly CompanyService $companyService)
    {
    }

    // -------------------------------------------------------------------------
    // GET /admin/companies
    // Paginated list with optional ?status=, ?search=
    // -------------------------------------------------------------------------
    public function index(Request $request): JsonResponse
    {
        $query = Company::with('user');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(['data' => $query->paginate(15)]);
    }

    // -------------------------------------------------------------------------
    // POST /admin/companies
    // Admin creates a company on behalf of any user (user_id required)
    // -------------------------------------------------------------------------
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user      = User::findOrFail($validated['user_id']);
        $company   = $this->companyService->store($validated, $user);

        return response()->json([
            'message' => 'Company created successfully by admin.',
            'data'    => $company,
        ], 201);
    }

    // -------------------------------------------------------------------------
    // GET /admin/companies/{company}
    // -------------------------------------------------------------------------
    public function show(Company $company): JsonResponse
    {
        return response()->json(['data' => $company->load('user')]);
    }

    // -------------------------------------------------------------------------
    // PUT /admin/companies/{company}
    // -------------------------------------------------------------------------
    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        $updated = $this->companyService->update($request->validated(), $company);

        return response()->json([
            'message' => 'Company updated successfully by admin.',
            'data'    => $updated,
        ]);
    }

    // -------------------------------------------------------------------------
    // PATCH /admin/companies/{company}/status
    // Body: { status: 'pending'|'approved'|'rejected'|'suspended' }
    // -------------------------------------------------------------------------
    public function updateStatus(Request $request, Company $company): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,approved,rejected,suspended'],
        ]);

        $isApproved = $validated['status'] === 'approved';

        $company->update([
            'status'      => $validated['status'],
            'is_verified' => $isApproved,
            'verified_at' => $isApproved ? now() : null,
        ]);

        return response()->json([
            'message' => 'Company status updated successfully.',
            'data'    => $company->fresh(),
        ]);
    }

    // -------------------------------------------------------------------------
    // DELETE /admin/companies/{company}
    // -------------------------------------------------------------------------
    public function destroy(Company $company): JsonResponse
    {
        $company->delete();

        return response()->json(['message' => 'Company deleted successfully.']);
    }
}
