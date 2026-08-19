<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Tells the admins that someone is waiting to be verified.
 *
 * Nothing announced a submission before, so the only way to find out was to
 * open the panel and look — which is how 87 requests ended up sitting there.
 */
class VerificationSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(protected User $user)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $category = $this->user->category;

        return [
            'type' => 'verification_submitted',
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'category' => $category
                ? ($category->display_name_ar ?: $category->name_ar ?: $category->name_en)
                : null,
            'message' => __('admin.notifications.verification_submitted', [
                'name' => $this->user->name,
            ], 'ar'),
            'url' => route('admin.users.show', $this->user->id),
        ];
    }

    /**
     * Title and body for the push, kept next to the database payload so the
     * two can never describe different things.
     *
     * @return array{title: string, body: string, url: string}
     */
    public function pushPayload(): array
    {
        return [
            'title' => __('admin.notifications.verification_push_title', [], 'ar'),
            'body' => __('admin.notifications.verification_submitted', [
                'name' => $this->user->name,
            ], 'ar'),
            'url' => route('admin.users.show', $this->user->id),
        ];
    }
}
