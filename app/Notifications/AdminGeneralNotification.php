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
    protected $sendPush;

    /**
     * @param  bool  $sendPush  false when the caller pushes to OneSignal itself
     *                          in one batched request (see the admin broadcast).
     *                          The database record is written either way, since
     *                          that is what the in-app notifications list reads.
     */
    public function __construct($title, $message, $imageUrl = null, $metaData = [], $sendPush = true)
    {
        $this->title = $title;
        $this->message = $message;
        $this->imageUrl = $imageUrl;
        $this->metaData = $metaData;
        $this->sendPush = $sendPush;
    }

    public function via($notifiable): array
    {
        return $this->sendPush ? ['database', OneSignalChannel::class] : ['database'];
    }

    /**
     * The OneSignal payload, exposed so a batched send reuses exactly the same
     * body (image fields included) as the per-user path.
     *
     * @return array
     */
    public function oneSignalPayload(): array
    {
        return $this->toOneSignal(null);
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
            // Flat, so tapping the row in the in-app list reaches the same
            // target keys the push carries. `meta_data` stays alongside it
            // because that is the shape older builds were handed.
            $data = array_merge($data, $this->metaData);
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
            $payload['ios_attachments'] = ['bg' => $this->imageUrl];
        }

        return $payload;
    }
}
