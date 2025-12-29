<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequested extends Notification
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
            'title' => 'New Service Request',
            'body' => 'You have a new request for ' . $this->serviceRequest->service->title,
            'service_request_id' => $this->serviceRequest->id,
            'type' => 'service_request'
        ];
    }
}
