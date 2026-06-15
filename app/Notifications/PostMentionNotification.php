<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostMentionNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $post;
    public $mentioner;

    public function __construct($post, $mentioner)
    {
        $this->post = $post;
        $this->mentioner = $mentioner;
    }

    public function via(object $notifiable): array
    {
        return ['database', \App\Channels\OneSignalChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'تم ذكرك في منشور جديد',
            'body' => "قام {$this->mentioner->name} بذكرك في منشور.",
            'type' => 'post_mention',
            'post_id' => $this->post->id,
            'user_id' => $this->mentioner->id,
            'user_avatar' => $this->mentioner->avatar ? asset('storage/' . $this->mentioner->avatar) : null,
        ];
    }
}
