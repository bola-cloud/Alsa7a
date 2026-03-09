<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Channels\OneSignalChannel;

class RatingNotification extends Notification
{
    use Queueable;

    protected $rating;
    protected $reviewer;

    public function __construct($rating)
    {
        $this->rating = $rating;
        $this->reviewer = $rating->reviewer;
    }

    public function via($notifiable): array
    {
        return ['database', OneSignalChannel::class];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'New Rating Received',
            'body' => "{$this->reviewer->name} rated you {$this->rating->rating} stars.",
            'type' => 'rating',
            'rating' => $this->rating->rating,
            'comment' => $this->rating->comment,
            'reviewer_id' => $this->reviewer->id,
        ];
    }

    public function toOneSignal($notifiable): array
    {
        return [
            'title' => ['en' => 'New Rating Received', 'ar' => 'تقييم جديد'],
            'message' => [
                'en' => "{$this->reviewer->name} rated you {$this->rating->rating} stars.",
                'ar' => "قام {$this->reviewer->name} بتقييمك بـ {$this->rating->rating} نجوم."
            ],
            'data' => [
                'type' => 'rating',
                'reviewer_id' => $this->reviewer->id,
            ],
        ];
    }
}
