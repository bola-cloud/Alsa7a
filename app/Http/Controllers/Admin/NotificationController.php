<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->take(10)->get();
        $unreadCount = $user->unreadNotifications->count();
        
        return response()->json([
            'count' => $unreadCount,
            'notifications' => $notifications->map(function($n) {
                return [
                    'id' => $n->id,
                    'read_at' => $n->read_at,
                    'data' => array_merge($n->data, [
                        'registered_text' => __('admin.notifications.registered')
                    ]),
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            })
        ]);
    }

    public function markAsRead(Request $request)
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function markSingleAsRead($id)
    {
        $notification = auth()->user()->unreadNotifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->json(['success' => true]);
    }
}
