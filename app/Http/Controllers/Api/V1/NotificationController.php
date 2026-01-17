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
        $notifications = $request->user()->notifications()->paginate(20);

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
