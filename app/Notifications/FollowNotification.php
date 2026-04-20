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
            'title' => ['en' => 'New Follower', 'ar' => 'متابع جديد'],
            'body' => [
                'en' => "{$this->follower->display_name_en} started following you.",
                'ar' => "قام {$this->follower->display_name_ar} بمتابعتك."
            ],
            'type' => 'follow',
            'follower_id' => $this->follower->id,
        ];
    }

    public function toOneSignal($notifiable): array
    {
        return [
            'title' => ['en' => 'New Follower', 'ar' => 'متابع جديد'],
            'message' => [
                'en' => "{$this->follower->display_name_en} started following you.",
                'ar' => "قام {$this->follower->display_name_ar} بمتابعتك."
            ],
            'data' => [
                'type' => 'follow',
                'follower_id' => $this->follower->id,
            ],
        ];
    }
}
