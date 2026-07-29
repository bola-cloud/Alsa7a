<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Channels\OneSignalChannel;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminGeneralNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $title;
    protected $message;
    protected $imageUrl;
    protected $metaData;

    public function __construct($title, $message, $imageUrl = null, $metaData = [])
    {
        $this->title = $title;
        $this->message = $message;
        $this->imageUrl = $imageUrl;
        $this->metaData = $metaData;
    }

    public function via($notifiable): array
    {
        return ['database', OneSignalChannel::class];
    }

    public function toArray($notifiable): array
    {
        $data = [
            'title' => ['en' => $this->title, 'ar' => $this->title],
            'body' => [
                'en' => $this->message,
                'ar' => $this->message
            ],
            'type' => 'admin_announcement',
        ];

        if ($this->imageUrl) {
            $data['image_url'] = $this->imageUrl;
        }
        
        if (!empty($this->metaData)) {
            $data['meta_data'] = $this->metaData;
        }

        return $data;
    }

    public function toOneSignal($notifiable): array
    {
        $payload = [
            'title' => ['en' => $this->title, 'ar' => $this->title],
            'message' => [
                'en' => $this->message,
                'ar' => $this->message
            ],
            'data' => array_merge(['type' => 'admin_announcement'], $this->metaData ?? []),
        ];

        if ($this->imageUrl) {
            $payload['big_picture'] = $this->imageUrl;
            $payload['ios_attachments'] = ['id1' => $this->imageUrl];
        }

        return $payload;
    }
}
