<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequestNotification extends Notification
{
    use Queueable;

    public $data;

    /**
     * Create a new notification instance.
     *
     * @param array $data Structure: ['title' => '', 'body' => '', 'type' => '', 'request_id' => '']
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', \App\Channels\OneSignalChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->data['title'],
            'body' => $this->data['body'],
            'type' => $this->data['type'] ?? 'info', // e.g., 'new_request', 'status_update'
            'request_id' => $this->data['request_id'] ?? null,
            'service_id' => $this->data['service_id'] ?? null,
            'sender_id' => $this->data['sender_id'] ?? null, // User who triggered it
        ];
    }

    public function toOneSignal($notifiable): array
    {
        return [
            'title' => $this->data['push_title'] ?? ['en' => $this->data['title'], 'ar' => $this->data['title']],
            'message' => $this->data['push_body'] ?? ['en' => $this->data['body'], 'ar' => $this->data['body']],
            'data' => [
                'type' => $this->data['type'] ?? 'info',
                'request_id' => $this->data['request_id'] ?? null,
            ],
        ];
    }
}
