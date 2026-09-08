<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FailedJob;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Fetch DB notifications if any
        $dbNotifications = $user->notifications()
            ->latest('created_at')
            ->limit(20)
            ->get();

        $notifications = $dbNotifications->map(function (DatabaseNotification $n) {
            return [
                'id' => (string) $n->id,
                'title' => $n->data['title'] ?? 'System Notification',
                'message' => $n->data['message'] ?? ($n->data['body'] ?? 'No details provided'),
                'category' => $n->data['category'] ?? 'system',
                'level' => $n->data['level'] ?? 'info',
                'link' => $n->data['link'] ?? null,
                'read' => $n->read_at !== null,
                'created_at' => $n->created_at?->toIso8601String() ?? Carbon::now()->toIso8601String(),
            ];
        })->toArray();

        // Augment with real-time operational & security alerts
        $pendingCompanies = Company::query()->where('is_verified', false)->orWhereNull('is_verified')->count();
        if ($pendingCompanies > 0) {
            $notifications[] = [
                'id' => 'sec_pending_companies',
                'title' => 'Pending Company Verifications',
                'message' => "{$pendingCompanies} company profile(s) awaiting verification and review.",
                'category' => 'security',
                'level' => 'warning',
                'link' => '/companies',
                'read' => false,
                'created_at' => Carbon::now()->subMinutes(15)->toIso8601String(),
            ];
        }

        $failedJobsCount = FailedJob::query()->count();
        if ($failedJobsCount > 0) {
            $notifications[] = [
                'id' => 'sec_failed_jobs',
                'title' => 'Queue Failure Alert',
                'message' => "{$failedJobsCount} background queue job(s) failed and need review.",
                'category' => 'system',
                'level' => 'danger',
                'link' => '/admin-resources/failed-jobs',
                'read' => false,
                'created_at' => Carbon::now()->subMinutes(30)->toIso8601String(),
            ];
        }

        $recentAdmins = User::query()->where('role', 'admin')->latest()->limit(3)->get();
        if ($recentAdmins->isNotEmpty()) {
            $adminNames = $recentAdmins->pluck('name')->join(', ');
            $notifications[] = [
                'id' => 'sec_admin_access',
                'title' => 'Admin Privileged Access',
                'message' => "Authenticated admin session active for {$adminNames}.",
                'category' => 'security',
                'level' => 'info',
                'link' => '/security',
                'read' => true,
                'created_at' => Carbon::now()->subHours(2)->toIso8601String(),
            ];
        }

        $recentDraftJobs = JobPost::query()->where('status', 'draft')->count();
        if ($recentDraftJobs > 0) {
            $notifications[] = [
                'id' => 'rec_draft_jobs',
                'title' => 'Draft Job Posts',
                'message' => "{$recentDraftJobs} draft job post(s) currently unpublished.",
                'category' => 'recruitment',
                'level' => 'info',
                'link' => '/job-posts',
                'read' => false,
                'created_at' => Carbon::now()->subHours(4)->toIso8601String(),
            ];
        }

        $unreadCount = collect($notifications)->where('read', false)->count();

        return response()->json([
            'status' => 'success',
            'data' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->first();
        if ($notification && $notification->read_at === null) {
            $notification->markAsRead();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read.',
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read.',
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->first();
        if ($notification) {
            $notification->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notification removed.',
        ]);
    }
}
