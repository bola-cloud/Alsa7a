<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Channels\OneSignalChannel;

class TicketStatusNotification extends Notification
{
    use Queueable;

    protected $ticket;
    protected $status;

    public function __construct($ticket)
    {
        $this->ticket = $ticket;
        $this->status = $ticket->status;
    }

    public function via($notifiable): array
    {
        return ['database', OneSignalChannel::class];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Ticket Status Updated',
            'body' => "Your ticket #{$this->ticket->id} status has been updated to {$this->status}.",
            'type' => 'ticket_status',
            'ticket_id' => $this->ticket->id,
            'status' => $this->status,
        ];
    }

    public function toOneSignal($notifiable): array
    {
        return [
            'title' => ['en' => 'Ticket Status Updated', 'ar' => 'تم تحديث حالة التذكرة'],
            'message' => [
                'en' => "Your ticket #{$this->ticket->id} status has been updated to {$this->status}.",
                'ar' => "تم تحديث حالة تذكرتك رقم #{$this->ticket->id} إلى {$this->status}."
            ],
            'data' => [
                'type' => 'ticket_status',
                'ticket_id' => $this->ticket->id,
            ],
        ];
    }
}
