<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()
            ->latest('created_at')
            ->paginate(20);

        return response()->json([
            'data' => $notifications->getCollection()->map(
                fn (DatabaseNotification $notification): array => $this->transform($notification),
            )->values(),
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function markAsRead(Request $request, string $notification): JsonResponse
    {
        $userNotification = $request->user()->notifications()->whereKey($notification)->first();

        if (!$userNotification) {
            throw (new ModelNotFoundException())->setModel(DatabaseNotification::class, [$notification]);
        }

        if ($userNotification->read_at === null) {
            $userNotification->markAsRead();
        }

        return response()->json([
            'message' => 'Notification marked as read.',
            'data' => $this->transform($userNotification->fresh()),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read.',
        ]);
    }

    public function destroy(Request $request, string $notification): JsonResponse
    {
        $userNotification = $request->user()->notifications()->whereKey($notification)->first();

        if ($userNotification) {
            $userNotification->delete();
        }

        return response()->json([
            'message' => 'Notification deleted.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function transform(DatabaseNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'data' => $notification->data,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
        ];
    }
}
