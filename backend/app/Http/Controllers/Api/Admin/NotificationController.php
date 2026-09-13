<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ContactMessage;
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
        
        // 1. Fetch real DB notifications from notifications table
        $dbNotifications = $user->notifications()
            ->latest('created_at')
            ->limit(30)
            ->get();

        $notifications = $dbNotifications->map(function (DatabaseNotification $n) {
            $data = $n->data ?? [];
            return [
                'id' => (string) $n->id,
                'title' => $data['title'] ?? 'System Notification',
                'message' => $data['message'] ?? ($data['body'] ?? 'No details provided'),
                'body' => $data['body'] ?? ($data['message'] ?? null),
                'sender_name' => $data['sender_name'] ?? null,
                'sender_email' => $data['sender_email'] ?? null,
                'sender_phone' => $data['sender_phone'] ?? null,
                'subject' => $data['subject'] ?? null,
                'inquiry_label' => $data['inquiry_label'] ?? null,
                'category' => $data['category'] ?? 'system',
                'level' => $data['level'] ?? 'info',
                'link' => $data['link'] ?? null,
                'read' => $n->read_at !== null,
                'created_at' => $n->created_at?->toIso8601String() ?? Carbon::now()->toIso8601String(),
            ];
        })->toArray();

        // 2. Fetch any un-notified or recent Contact Messages directly from contact_messages table
        $recentMessages = ContactMessage::query()->latest()->limit(15)->get();
        foreach ($recentMessages as $msg) {
            // Avoid duplicate if already in $notifications
            $exists = collect($notifications)->contains(function ($item) use ($msg) {
                return isset($item['contact_message_id']) && $item['contact_message_id'] == $msg->id;
            });

            if (!$exists) {
                $inquiryLabel = match ($msg->subject_type) {
                    'candidate' => 'Candidate Support',
                    'employer' => 'Employer / Hiring Inquiry',
                    'tech' => 'Technical Support Issue',
                    default => 'General Inquiry',
                };

                $notifications[] = [
                    'id' => 'msg_' . $msg->id,
                    'contact_message_id' => $msg->id,
                    'title' => "Message: {$msg->subject}",
                    'message' => "From {$msg->name} ({$msg->email}): " . \Illuminate\Support\Str::limit($msg->message, 120),
                    'body' => $msg->message,
                    'sender_name' => $msg->name,
                    'sender_email' => $msg->email,
                    'sender_phone' => $msg->phone,
                    'subject' => $msg->subject,
                    'inquiry_label' => $inquiryLabel,
                    'category' => 'message',
                    'level' => 'info',
                    'link' => '/admin-resources/messages',
                    'read' => (bool) $msg->is_read,
                    'created_at' => $msg->created_at?->toIso8601String() ?? Carbon::now()->toIso8601String(),
                ];
            }
        }

        // 3. System Health Alerts
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
                'created_at' => Carbon::now()->subMinutes(15)->toIso8601String(),
            ];
        }

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
                'created_at' => Carbon::now()->subMinutes(30)->toIso8601String(),
            ];
        }

        // Sort by created_at desc
        usort($notifications, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        $unreadCount = collect($notifications)->where('read', false)->count();

        return response()->json([
            'status' => 'success',
            'data' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        if (str_starts_with($id, 'msg_')) {
            $msgId = (int) str_replace('msg_', '', $id);
            ContactMessage::where('id', $msgId)->update(['is_read' => true]);
        } else {
            $notification = $request->user()->notifications()->whereKey($id)->first();
            if ($notification && $notification->read_at === null) {
                $notification->markAsRead();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read.',
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();
        ContactMessage::where('is_read', false)->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read.',
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if (str_starts_with($id, 'msg_')) {
            $msgId = (int) str_replace('msg_', '', $id);
            ContactMessage::where('id', $msgId)->delete();
        } else {
            $notification = $request->user()->notifications()->whereKey($id)->first();
            if ($notification) {
                $notification->delete();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notification removed.',
        ]);
    }
}
