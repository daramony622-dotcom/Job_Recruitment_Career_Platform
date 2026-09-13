<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display company-scoped report data for the HR/company dashboard.
     */
    public function index(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not associated with any company.',
            ], 403);
        }

        $jobStatusCounts = JobPost::query()
            ->where('company_id', $company->id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->orderBy('status')
            ->pluck('count', 'status');

        $applicationStatusCounts = Application::query()
            ->forCompany($company->id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->orderBy('status')
            ->pluck('count', 'status');

        $applicationQuery = Application::query()->forCompany($company->id);
        $candidateIds = (clone $applicationQuery)->select('user_id');

        $candidateQuery = User::query()->whereIn('id', $candidateIds);

        return response()->json([
            'status' => 'success',
            'data' => [
                'summary' => [
                    'total_jobs' => JobPost::query()->where('company_id', $company->id)->count(),
                    'published_jobs' => (int) ($jobStatusCounts['published'] ?? 0),
                    'draft_jobs' => (int) ($jobStatusCounts['draft'] ?? 0),
                    'closed_jobs' => (int) ($jobStatusCounts['closed'] ?? 0)
                        + (int) ($jobStatusCounts['suspended'] ?? 0),
                    'total_candidates' => (clone $candidateQuery)->count(),
                    'active_candidates' => (clone $candidateQuery)
                        ->whereNotNull('email_verified_at')
                        ->count(),
                    'total_companies' => Company::query()->where('id', $company->id)->count(),
                    'total_applications' => (clone $applicationQuery)->count(),
                ],
                'job_posts_by_status' => $jobStatusCounts,
                'applications_by_status' => $applicationStatusCounts,
                'recent_jobs' => JobPost::query()
                    ->where('company_id', $company->id)
                    ->with('company:id,name')
                    ->latest()
                    ->limit(5)
                    ->get(),
                'recent_applications' => Application::query()
                    ->forCompany($company->id)
                    ->with([
                        'jobPost:id,title',
                        'jobSeeker:id,name',
                    ])
                    ->latest()
                    ->limit(5)
                    ->get(),
            ],
        ]);
    }
}
