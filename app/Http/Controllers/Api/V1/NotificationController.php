<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get user notifications.
     * GET /api/v1/notifications
     */
    public function index(Request $request)
    {
        // `notifications()` orders by created_at alone, and that column is
        // second-precision — two notifications in the same second tie, and a
        // tie straddling a page boundary makes MySQL repeat one row on both
        // pages and drop the other. The id breaks the tie deterministically.
        $notifications = $request->user()->notifications()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20);
        $locale = app()->getLocale() ?? 'en';

        $notifications->getCollection()->transform(function ($n) use ($locale) {
            $data = $n->data;
            
            // Handle translated titles
            if (isset($data['title']) && is_array($data['title'])) {
                $data['title'] = $data['title'][$locale] ?? $data['title']['en'] ?? '';
            }
            
            // Handle translated bodies
            if (isset($data['body']) && is_array($data['body'])) {
                $data['body'] = $data['body'][$locale] ?? $data['body']['en'] ?? '';
            }
            
            $n->data = $data;
            return $n;
        });

        return response()->json([
            'status' => true,
            'data' => $notifications,
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'message' => 'Notifications retrieved successfully'
        ]);
    }

    /**
     * Mark a notification as read.
     * POST /api/v1/notifications/{id}/read
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'status' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read.
     * POST /api/v1/notifications/read-all
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'status' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Backward compatibility wrapper for markAllAsRead.
     */
    public function markAllRead(Request $request)
    {
        return $this->markAllAsRead($request);
    }

    /**
     * Delete a notification.
     * DELETE /api/v1/notifications/{id}
     */
    public function destroy(Request $request, $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->delete();
            return response()->json(['status' => true, 'message' => 'Notification deleted']);
        }

        return response()->json(['status' => false, 'message' => 'Notification not found'], 404);
    }
}
