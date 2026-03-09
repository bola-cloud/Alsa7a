<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Channels\OneSignalChannel;

class ClubRequestNotification extends Notification
{
    use Queueable;

    protected $clubRequest;
    protected $data;

    public function __construct($clubRequest, $data = [])
    {
        $this->clubRequest = $clubRequest;
        $this->data = $data;
    }

    public function via($notifiable): array
    {
        return ['database', OneSignalChannel::class];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->data['title'] ?? 'Club Request',
            'body' => $this->data['body'] ?? 'You have a new club invitation or request.',
            'type' => 'club_request',
            'club_request_id' => $this->clubRequest->id,
            'status' => $this->clubRequest->status,
        ];
    }

    public function toOneSignal($notifiable): array
    {
        return [
            'title' => $this->data['push_title'] ?? ['en' => 'Club Request', 'ar' => 'طلب انضمام لنادي'],
            'message' => $this->data['push_body'] ?? [
                'en' => 'You have a new club invitation or request.',
                'ar' => 'لديك دعوة أو طلب انضمام جديد لنادي.'
            ],
            'data' => [
                'type' => 'club_request',
                'club_request_id' => $this->clubRequest->id,
            ],
        ];
    }
}
