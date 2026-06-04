<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Channels\OneSignalChannel;

class AdminGeneralNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $message;

    public function __construct($title, $message)
    {
        $this->title = $title;
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        return ['database', OneSignalChannel::class];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => ['en' => $this->title, 'ar' => $this->title],
            'body' => [
                'en' => $this->message,
                'ar' => $this->message
            ],
            'type' => 'admin_announcement',
        ];
    }

    public function toOneSignal($notifiable): array
    {
        return [
            'title' => ['en' => $this->title, 'ar' => $this->title],
            'message' => [
                'en' => $this->message,
                'ar' => $this->message
            ],
            'data' => [
                'type' => 'admin_announcement',
            ],
        ];
    }
}
