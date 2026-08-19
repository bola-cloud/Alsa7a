<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as Notifier;

/**
 * One way to reach the people who run the panel.
 *
 * An alert has to arrive twice over: as a row behind the bell, which is what
 * the panel reads, and as a push, which is the only part that reaches someone
 * who is not looking at the panel. Keeping both behind a single call means a
 * new alert cannot accidentally ship with only half of that.
 */
class AdminNotifier
{
    /** Tag the panel puts on a subscription once an admin allows notifications. */
    public const PANEL_TAG_KEY = 'panel_role';
    public const PANEL_TAG_VALUE = 'admin';

    /**
     * Everyone who should hear about it.
     *
     * `is_admin` is the real flag; the seeded super admin is matched by email
     * as well because that account predates the flag.
     */
    public static function recipients(): Collection
    {
        return User::query()
            ->where('is_admin', true)
            ->orWhere('email', 'admin@alsa7a.com')
            ->get();
    }

    /**
     * Writes the bell rows and, when the notification offers a push payload,
     * sends one push to every admin browser that opted in.
     *
     * The push is a single request aimed by tag — the same rule the rest of the
     * app follows: per-recipient sends are for personal notifications only.
     */
    public static function alert(Notification $notification): int
    {
        $admins = static::recipients();

        if ($admins->isEmpty()) {
            Log::warning('AdminNotifier: no admin recipients found.');
            return 0;
        }

        Notifier::send($admins, $notification);

        if (method_exists($notification, 'pushPayload')) {
            static::push($notification->pushPayload());
        }

        return $admins->count();
    }

    /**
     * @param  array{title: string, body: string, url?: string}  $payload
     */
    protected static function push(array $payload): void
    {
        try {
            $oneSignal = app(OneSignalService::class);

            $result = $oneSignal->sendToFilters(
                [[
                    'field' => 'tag',
                    'key' => static::PANEL_TAG_KEY,
                    'relation' => '=',
                    'value' => static::PANEL_TAG_VALUE,
                ]],
                $payload['title'],
                $payload['body'],
                array_filter([
                    'type' => 'admin_alert',
                    'url' => $payload['url'] ?? null,
                ]),
                array_filter([
                    // Opens the panel page directly when the push is clicked in
                    // a browser; ignored by the mobile SDK.
                    'url' => $payload['url'] ?? null,

                    // Delivered now rather than batched with whatever else the
                    // device is waiting on. An admin alert is the kind of thing
                    // that stops being useful if it arrives late.
                    'priority' => 10,

                    // Still worth showing if the device was off for a while;
                    // a pending request does not expire in an hour.
                    'ttl' => 86400,

                    // An icon and an action turn it from a line of text into
                    // something that reads as an alert and can be acted on in
                    // one tap.
                    // No badge on purpose: the badge is the small monochrome
                    // silhouette in the status bar, and a full-colour logo there
                    // renders as a solid blob. Chrome's own default reads better.
                    'chrome_web_icon' => asset('app-assets/images/notification-icon.png'),
                    'web_buttons' => [[
                        'id' => 'review',
                        'text' => __('admin.notifications.push_action_review', [], 'ar'),
                        'url' => $payload['url'] ?? url('/admin'),
                    ]],
                ])
            );

            if (! ($result['status'] ?? false)) {
                Log::warning('AdminNotifier: push failed', ['result' => $result]);
            }
        } catch (\Throwable $e) {
            // A push that cannot go out must never fail the request that caused
            // it — the bell row is already written either way.
            Log::error('AdminNotifier: push threw ' . $e->getMessage());
        }
    }
}
