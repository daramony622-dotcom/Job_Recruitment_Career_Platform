<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display platform-wide report data for the admin dashboard.
     */
    public function index(): JsonResponse
    {
        $jobStatusCounts = JobPost::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->orderBy('status')
            ->pluck('count', 'status');

        $applicationStatusCounts = Application::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->orderBy('status')
            ->pluck('count', 'status');

        $candidateQuery = User::query()->whereIn('role', ['user', 'job_seeker']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'summary' => [
                    'total_jobs' => JobPost::query()->count(),
                    'published_jobs' => (int) ($jobStatusCounts['published'] ?? 0),
                    'draft_jobs' => (int) ($jobStatusCounts['draft'] ?? 0),
                    'closed_jobs' => (int) ($jobStatusCounts['closed'] ?? 0)
                        + (int) ($jobStatusCounts['suspended'] ?? 0),
                    'total_candidates' => (clone $candidateQuery)->count(),
                    'active_candidates' => (clone $candidateQuery)
                        ->whereNotNull('email_verified_at')
                        ->count(),
                    'total_companies' => Company::query()->count(),
                    'total_applications' => Application::query()->count(),
                ],
                'job_posts_by_status' => $jobStatusCounts,
                'applications_by_status' => $applicationStatusCounts,
                'recent_jobs' => JobPost::query()
                    ->with('company:id,name')
                    ->latest()
                    ->limit(5)
                    ->get(),
                'recent_applications' => Application::query()
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
