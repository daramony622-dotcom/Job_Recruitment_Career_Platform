<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobCategory;
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

        // Monthly trends (past 6 months)
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $monthKey = $monthDate->format('Y-m');
            $label = $monthDate->format('M');
            
            $appsCount = Application::query()
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->count();

            $jobsCount = JobPost::query()
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->count();

            $monthlyTrends[] = [
                'month' => $label,
                'key' => $monthKey,
                'applications' => $appsCount,
                'jobs' => $jobsCount,
            ];
        }

        // Category progress
        $categoryProgress = JobCategory::query()
            ->withCount('jobPosts')
            ->limit(5)
            ->get()
            ->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'jobs_count' => $cat->job_posts_count,
                    'progress' => min(100, max(15, $cat->job_posts_count * 15)),
                ];
            });

        $hiredCount = (int) ($applicationStatusCounts['hired'] ?? 0);
        $shortlistedCount = (int) ($applicationStatusCounts['shortlisted'] ?? 0);
        $totalApps = Application::query()->count();
        $conversionRate = $totalApps > 0 ? round((($hiredCount + $shortlistedCount) / $totalApps) * 100, 1) : 0;

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
                    'total_applications' => $totalApps,
                    'conversion_rate' => $conversionRate,
                ],
                'job_posts_by_status' => $jobStatusCounts,
                'applications_by_status' => $applicationStatusCounts,
                'monthly_trends' => $monthlyTrends,
                'category_progress' => $categoryProgress,
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
