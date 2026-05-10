<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->unreadNotifications;
        
        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications->map(function($n) {
                return [
                    'id' => $n->id,
                    'data' => $n->data,
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
}
