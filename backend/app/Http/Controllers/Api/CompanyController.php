<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompanyController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $companies = Company::query()
            ->where('status', 'approved')
            ->withCount(['jobs as open_jobs_count' => function ($query) {
                $query->published();
            }])
            ->latest()
            ->paginate(15);

        return CompanyResource::collection($companies);
    }

    public function show(Company $company): CompanyResource
    {
        abort_unless($company->status === 'approved', 404);

        $company->load([
            'jobs' => fn ($query) => $query->published()->with('category'),
        ]);

        return new CompanyResource($company);
    }
}