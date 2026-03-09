<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Services\OneSignalService;

class OneSignalChannel
{
    protected $oneSignal;

    public function __construct(OneSignalService $oneSignal)
    {
        $this->oneSignal = $oneSignal;
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toOneSignal')) {
            return;
        }

        $data = $notification->toOneSignal($notifiable);

        if (empty($data)) {
            return;
        }

        // Get Player ID from notifiable
        $playerId = $notifiable->onesignal_subscription['id'] ?? null;

        if (!$playerId) {
            return;
        }

        return $this->oneSignal->sendToPlayers(
            [$playerId],
            $data['title'],
            $data['message'],
            $data['data'] ?? null
        );
    }
}
