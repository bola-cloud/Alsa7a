<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Channels\OneSignalChannel;

class MessageNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $sender;

    public function __construct($message)
    {
        $this->message = $message;
        $this->sender = $message->sender;
    }

    public function via($notifiable): array
    {
        return ['database', OneSignalChannel::class];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'New Message',
            'body' => "{$this->sender->display_name}: {$this->message->body}",
            'type' => 'message',
            'conversation_id' => $this->message->conversation_id,
            'message_id' => $this->message->id,
            'sender_id' => $this->sender->id,
        ];
    }

    public function toOneSignal($notifiable): array
    {
        return [
            'title' => ['en' => 'New Message', 'ar' => 'رسالة جديدة'],
            'message' => [
                'en' => "{$this->sender->display_name_en}: {$this->message->body}",
                'ar' => "{$this->sender->display_name_ar}: {$this->message->body}"
            ],
            'data' => [
                'type' => 'message',
                'conversation_id' => $this->message->conversation_id,
                'sender_id' => $this->sender->id,
            ],
        ];
    }
}
