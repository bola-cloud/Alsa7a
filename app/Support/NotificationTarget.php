<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Where an admin announcement sends the user when they tap it.
 *
 * One table drives the dropdown, the validation, the push payload and the
 * preview link, so the four can never drift apart. Adding a destination is a
 * single entry here plus a label in `admin.notifications.target_option_*`.
 *
 * `app_type` is the value the mobile app switches on
 * (`NavigationService._navigateTo`); `path` is the matching `/share/...`
 * segment, used only to render a human-readable preview.
 */
class NotificationTarget
{
    public const NONE = 'none';
    public const URL = 'url';

    /** Destinations that take no id — the app opens a whole section. */
    public const INPUT_NONE = 'none';

    /** Destinations that take a numeric id of an existing row. */
    public const INPUT_ID = 'id';

    /** The external-link destination. */
    public const INPUT_URL = 'url';

    /**
     * @return array<string, array{input: string, app_type: ?string, table?: string, where?: array, path?: string}>
     */
    public static function all(): array
    {
        return [
            self::NONE => ['input' => self::INPUT_NONE, 'app_type' => null],
            self::URL => ['input' => self::INPUT_URL, 'app_type' => 'url'],

            // Single items — the id must exist, otherwise the user taps into a
            // "couldn't open" toast.
            'post' => ['input' => self::INPUT_ID, 'app_type' => 'post', 'table' => 'posts', 'path' => 'post'],
            'community_post' => ['input' => self::INPUT_ID, 'app_type' => 'community_post', 'table' => 'community_posts', 'path' => 'community'],
            'reel' => ['input' => self::INPUT_ID, 'app_type' => 'reel', 'table' => 'posts', 'where' => ['type' => 'video'], 'path' => 'reel'],
            'news' => ['input' => self::INPUT_ID, 'app_type' => 'news', 'table' => 'news', 'path' => 'news'],
            'event' => ['input' => self::INPUT_ID, 'app_type' => 'event', 'table' => 'events', 'path' => 'event'],
            'service' => ['input' => self::INPUT_ID, 'app_type' => 'service', 'table' => 'services', 'path' => 'service'],
            'job' => ['input' => self::INPUT_ID, 'app_type' => 'job', 'table' => 'market_requests', 'path' => 'job'],

            // A person, and the three pages hanging off their profile.
            'profile' => ['input' => self::INPUT_ID, 'app_type' => 'profile', 'table' => 'users', 'path' => 'profile'],
            'calendar' => ['input' => self::INPUT_ID, 'app_type' => 'calendar', 'table' => 'users', 'path' => 'calendar'],
            'user_services' => ['input' => self::INPUT_ID, 'app_type' => 'user_services', 'table' => 'users', 'path' => 'userservices'],
            'user_jobs' => ['input' => self::INPUT_ID, 'app_type' => 'user_jobs', 'table' => 'users', 'path' => 'userjobs'],

            // Whole sections — no id.
            'marketplace' => ['input' => self::INPUT_NONE, 'app_type' => 'marketplace', 'path' => 'marketplace'],
            'news_section' => ['input' => self::INPUT_NONE, 'app_type' => 'news', 'path' => 'news'],
            'reels_section' => ['input' => self::INPUT_NONE, 'app_type' => 'reels', 'path' => 'reels'],
            'home' => ['input' => self::INPUT_NONE, 'app_type' => 'home', 'path' => ''],
        ];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function definition(?string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    public static function inputFor(?string $key): string
    {
        return self::definition($key)['input'] ?? self::INPUT_NONE;
    }

    /**
     * Options for the dropdown, each carrying the input kind so the form can
     * show the right field without a hardcoded list in JavaScript.
     *
     * @return array<int, array{value: string, label: string, input: string, path: ?string}>
     */
    public static function options(): array
    {
        return array_map(fn ($key) => [
            'value' => $key,
            'label' => __('admin.notifications.target_option_' . $key),
            'input' => self::inputFor($key),
            'path' => self::definition($key)['path'] ?? null,
        ], self::keys());
    }

    /**
     * Validation rules for the three form fields. The id rule is conditional
     * because "which table" only becomes known once a destination is picked.
     */
    public static function rules(?string $selected): array
    {
        $input = self::inputFor($selected);

        return [
            'target_type' => ['nullable', Rule::in(self::keys())],
            'target_url' => $input === self::INPUT_URL
                ? ['required', 'url', 'max:2048', 'starts_with:http://,https://']
                : ['nullable', 'string', 'max:2048'],
            'target_id' => $input === self::INPUT_ID
                ? ['required', 'integer', 'min:1']
                : ['nullable', 'integer'],
        ];
    }

    /**
     * Does the id actually exist? Kept out of the rules array because a reel is
     * a `posts` row filtered by type, which `exists:` alone cannot express.
     */
    public static function idExists(?string $key, $id): bool
    {
        $definition = self::definition($key);

        if (! $definition || ($definition['input'] ?? null) !== self::INPUT_ID) {
            return true;
        }

        $query = DB::table($definition['table'])->where('id', $id);

        foreach ($definition['where'] ?? [] as $column => $value) {
            $query->where($column, $value);
        }

        return $query->exists();
    }

    /**
     * The keys added to the notification payload.
     *
     * `target_*` is a separate namespace from `type`, on purpose: `type` stays
     * "what kind of notification is this" (`admin_announcement`) and the target
     * keys say "where does tapping it go". Older app builds that only read
     * `type` keep behaving exactly as before.
     *
     * @return array<string, string>
     */
    public static function payload(?string $key, $id = null, ?string $url = null): array
    {
        $definition = self::definition($key);

        if (! $definition || $definition['app_type'] === null) {
            return [];
        }

        $payload = ['target_type' => $definition['app_type']];

        if ($definition['input'] === self::INPUT_URL) {
            if (! $url) {
                return [];
            }

            // `url` is repeated as a bare key because that is what the previous
            // meta_key/meta_value form produced; old builds already look there.
            return $payload + ['target_url' => $url, 'url' => $url];
        }

        if ($definition['input'] === self::INPUT_ID) {
            if (! $id) {
                return [];
            }

            $payload['target_id'] = (string) $id;
        }

        return $payload;
    }

    /**
     * Human-readable preview of where the tap lands — the same `/share/...`
     * link the deep-link handler accepts, so it can be pasted in a browser to
     * check the destination before sending.
     */
    public static function previewLink(?string $key, $id = null, ?string $url = null): ?string
    {
        $definition = self::definition($key);

        if (! $definition || $definition['app_type'] === null) {
            return null;
        }

        if ($definition['input'] === self::INPUT_URL) {
            return $url ?: null;
        }

        $path = trim((string) ($definition['path'] ?? ''), '/');
        $segments = array_filter([$path, $definition['input'] === self::INPUT_ID ? $id : null]);

        return rtrim(config('app.url'), '/') . '/share/' . implode('/', $segments);
    }
}
