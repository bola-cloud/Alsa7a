<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Channels\OneSignalChannel;

class FollowNotification extends Notification
{
    use Queueable;

    protected $follower;

    public function __construct($follower)
    {
        $this->follower = $follower;
    }

    public function via($notifiable): array
    {
        return ['database', OneSignalChannel::class];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'New Follower',
            'body' => "{$this->follower->name} started following you.",
            'type' => 'follow',
            'follower_id' => $this->follower->id,
        ];
    }

    public function toOneSignal($notifiable): array
    {
        return [
            'title' => ['en' => 'New Follower', 'ar' => 'متابع جديد'],
            'message' => [
                'en' => "{$this->follower->name} started following you.",
                'ar' => "قام {$this->follower->name} بمتابعتك."
            ],
            'data' => [
                'type' => 'follow',
                'follower_id' => $this->follower->id,
            ],
        ];
    }
}
