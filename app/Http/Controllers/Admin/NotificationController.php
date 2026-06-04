<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function create()
    {
        return view('admin.notifications.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Send push notification via OneSignal
        // We will broadcast it to all users using the OneSignal channel.
        // We can create an anonymous notification or iterate over users.
        
        // Let's create an AdminGeneralNotification class first or use the OneSignal API directly.
        // Actually, since we use OneSignalChannel, we can just use the facade or a custom notification class.
        $users = \App\Models\User::whereNotNull('onesignal_subscription')->get();
        \Illuminate\Support\Facades\Notification::send($users, new \App\Notifications\AdminGeneralNotification($request->title, $request->message));

        return redirect()->route('admin.notifications.create')->with('success', __('admin.notifications.sent_success'));
    }

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
