<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Channels\OneSignalChannel;

class PostInteractionNotification extends Notification
{
    use Queueable;

    protected $post;
    protected $data;

    public function __construct($post, $data = [])
    {
        $this->post = $post;
        $this->data = $data;
    }

    public function via($notifiable): array
    {
        return ['database', OneSignalChannel::class];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->data['title'] ?? 'Post Interaction',
            'body' => $this->data['body'] ?? 'Someone interacted with your post.',
            'type' => 'post_interaction',
            'post_id' => $this->post->id,
            'interaction_type' => $this->data['interaction_type'] ?? 'like',
        ];
    }

    public function toOneSignal($notifiable): array
    {
        return [
            'title' => $this->data['push_title'] ?? ['en' => 'Post Interaction', 'ar' => 'تفاعل مع المنشور'],
            'message' => $this->data['push_body'] ?? [
                'en' => 'Someone interacted with your post.',
                'ar' => 'قام شخص ما بالتفاعل مع منشورك.'
            ],
            'data' => [
                'type' => 'post_interaction',
                'post_id' => $this->post->id,
            ],
        ];
    }
}
