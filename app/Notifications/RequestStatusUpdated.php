<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestStatusUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $serviceRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Request Updated',
            'body' => 'Your request status is now: ' . $this->serviceRequest->status,
            'service_request_id' => $this->serviceRequest->id,
            'type' => 'request_status',
            'status' => $this->serviceRequest->status
        ];
    }
}
