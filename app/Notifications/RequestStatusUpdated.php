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
            'request_id' => $this->serviceRequest->id,
            'service_id' => $this->serviceRequest->service_id,
            'type' => 'status_update',
            'status' => $this->serviceRequest->status,
            'sender_id' => null // System or Provider
        ];
    }
}
