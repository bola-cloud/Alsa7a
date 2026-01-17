<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OneSignalService;

class NotificationController extends Controller
{
    protected $oneSignal;

    public function __construct(OneSignalService $oneSignal)
    {
        $this->oneSignal = $oneSignal;
    }

    /**
     * Show the form for creating a new notification.
     */
    public function create()
    {
        return view('admin.notifications.create');
    }

    /**
     * Store and send the notification.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:190',
            'message' => 'required|string',
        ]);

        $title = $request->input('title');
        $message = $request->input('message');

        // Send Broadcast to All Users
        $result = $this->oneSignal->sendBroadcast($title, $message);

        if ($result['status']) {
            return redirect()->route('admin.notifications.create')
                ->with('success', 'Notification sent successfully to all users!');
        } else {
            return redirect()->back()
                ->with('error', 'Failed to send notification. Check logs.')
                ->withInput();
        }
    }
}
