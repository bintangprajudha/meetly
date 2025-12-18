<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Get all notifications for authenticated user
     *
     * @return Response
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = Auth::user();
        
        $notifications = Notification::with(['actor'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function($notif) {
                return [
                    'id' => $notif->id,
                    'type' => $notif->type,
                    'actor' => $notif->actor ? [
                        'id' => $notif->actor->id,
                        'name' => $notif->actor->name,
                        'email' => $notif->actor->email,
                        'avatar' => $notif->actor->avatar,
                    ] : null,
                    'created_at' => $notif->created_at,
                    'read_at' => $notif->read_at,
                    'data' => $notif->data,
                    'notifiable_id' => $notif->notifiable_id, // Tambahkan ini untuk link ke post
                    'notifiable_type' => $notif->notifiable_type,
                ];
            });

        $unreadCount = $user->unreadNotifications()->count();

        return Inertia::render('Notification', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }

    /**
     * Get unread notifications (for dropdown/badge)
     *
     * @return JsonResponse
     */
    public function unread(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        
        $notifications = Notification::with(['actor'])
            ->where('user_id', $user->id)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($notif) {
                return [
                    'id' => $notif->id,
                    'type' => $notif->type,
                    'actor' => [
                        'id' => $notif->actor->id,
                        'name' => $notif->actor->name,
                        'email' => $notif->actor->email,
                        'avatar' => $notif->actor->avatar,
                    ],
                    'created_at' => $notif->created_at,
                    'read_at' => $notif->read_at,
                    'data' => $notif->data,
                    'notifiable_id' => $notif->notifiable_id,
                    'notifiable_type' => $notif->notifiable_type,
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'count' => $notifications->count()
        ]);
    }

    /**
     * Mark notification as read
     *
     * @param int $id
     * @return JsonResponse
     */
    public function markAsRead(int $id): JsonResponse
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);
        
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     *
     * @return JsonResponse
     */
    public function markAllAsRead(): JsonResponse
    {
        Notification::where('user_id', Auth::id())
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete notification
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);
        
        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get unread count (for badge)
     *
     * @return JsonResponse
     */
    public function unreadCount(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        
        $count = $user->unreadNotifications()->count();
        
        return response()->json(['count' => $count]);
    }
}
