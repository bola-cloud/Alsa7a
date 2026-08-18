<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    protected $appId;
    protected $restApiKey;
    protected $apiUrl = 'https://onesignal.com/api/v1/notifications';

    /** OneSignal's hard limit for include_player_ids in a single request. */
    protected const MAX_IDS_PER_REQUEST = 2000;

    /** The built-in segment holding every subscriber of this app. */
    protected const ALL_SUBSCRIBERS_SEGMENT = 'Total Subscriptions';

    public function __construct()
    {
        $this->appId = config('services.onesignal.app_id');
        $this->restApiKey = config('services.onesignal.rest_api_key');
    }

    /**
     * Send to specific players. This is for PERSONAL notifications only
     * (chat message, request status change, a follow...). Announcements go
     * through sendBroadcast() / sendToFilters() in a single request.
     *
     * @param array $playerIds Array of OneSignal Player IDs (UUIDs).
     * @param string|array $title Title of the notification (string or ['en' => '...', 'ar' => '...']).
     * @param string|array $message Body of the notification.
     * @param array|null $data Additional data payload.
     * @return array
     */
    public function sendToPlayers(array $playerIds, $title, $message, ?array $data = null, array $additionalOptions = [])
    {
        $playerIds = array_values(array_unique(array_filter($playerIds)));

        if (empty($playerIds)) {
            return ['status' => false, 'error' => 'No recipients'];
        }

        $headings = is_array($title) ? $title : ['en' => $title];
        $contents = is_array($message) ? $message : ['en' => $message];

        // OneSignal caps include_player_ids at 2000 per request, so a personal
        // notification aimed at a group is chunked rather than sent per user.
        $results = [];

        foreach (array_chunk($playerIds, self::MAX_IDS_PER_REQUEST) as $chunk) {
            $payload = array_merge([
                'app_id' => $this->appId,
                'include_player_ids' => $chunk,
                'headings' => $headings,
                'contents' => $contents,
            ], $data ? ['data' => $data] : [], $additionalOptions);

            $results[] = $this->sendRequest($payload);
        }

        // One chunk (the common case) keeps the exact old return shape.
        if (count($results) === 1) {
            return $results[0];
        }

        $failed = array_filter($results, fn ($r) => ! ($r['status'] ?? false));

        return [
            'status' => empty($failed),
            'batches' => count($results),
            'recipients' => count($playerIds),
            'data' => $results,
        ];
    }

    /**
     * Broadcast to every subscriber in ONE request.
     *
     * Broadcasts must never loop over users: that is what include_player_ids
     * is for, and it belongs to personal notifications (chat, request status
     * changes and so on), not announcements.
     *
     * @param string|array $title
     * @param string|array $message
     * @param array|null $data
     * @param array $additionalOptions  e.g. big_picture / ios_attachments
     * @return array
     */
    public function sendBroadcast($title, $message, ?array $data = null, array $additionalOptions = [])
    {
        return $this->sendRequest($this->buildPayload($title, $message, $data, $additionalOptions, [
            'included_segments' => [self::ALL_SUBSCRIBERS_SEGMENT],
        ]));
    }

    /**
     * Broadcast to the subscribers matching OneSignal tag filters — still one
     * request, however large the audience.
     *
     * The mobile app tags every user with country_id and category_id, so an
     * announcement can be aimed at, say, players in Oman without the backend
     * enumerating anybody.
     *
     * @param array $filters  OneSignal filter entries, already interleaved with operators
     * @return array
     */
    public function sendToFilters(array $filters, $title, $message, ?array $data = null, array $additionalOptions = [])
    {
        if (empty($filters)) {
            return $this->sendBroadcast($title, $message, $data, $additionalOptions);
        }

        return $this->sendRequest($this->buildPayload($title, $message, $data, $additionalOptions, [
            'filters' => array_values($filters),
        ]));
    }

    /**
     * Build the OneSignal filter list for a country and/or a set of categories.
     *
     * OneSignal evaluates filters left to right and every "OR" starts a new
     * group, so `country AND (a OR b)` has to be expanded into
     * `(country AND a) OR (country AND b)`.
     *
     * @param  int|string|null  $countryId  null/'all' for every country
     * @param  array<int>  $categoryIds  empty for every category
     * @return array
     */
    public function buildAudienceFilters($countryId = null, array $categoryIds = [])
    {
        $hasCountry = $countryId !== null && $countryId !== '' && $countryId !== 'all';
        $categoryIds = array_values(array_filter($categoryIds));

        if (! $hasCountry && empty($categoryIds)) {
            return [];
        }

        $tag = fn ($key, $value) => ['field' => 'tag', 'key' => $key, 'relation' => '=', 'value' => (string) $value];

        if (empty($categoryIds)) {
            return [$tag('country_id', $countryId)];
        }

        $filters = [];

        foreach ($categoryIds as $index => $categoryId) {
            if ($index > 0) {
                $filters[] = ['operator' => 'OR'];
            }

            if ($hasCountry) {
                $filters[] = $tag('country_id', $countryId);
                $filters[] = ['operator' => 'AND'];
            }

            $filters[] = $tag('category_id', $categoryId);
        }

        return $filters;
    }

    /**
     * Shared body for every send type.
     */
    protected function buildPayload($title, $message, ?array $data, array $additionalOptions, array $targeting)
    {
        return array_merge([
            'app_id' => $this->appId,
            'headings' => is_array($title) ? $title : ['en' => $title],
            'contents' => is_array($message) ? $message : ['en' => $message],
        ], $targeting, $data ? ['data' => $data] : [], $additionalOptions);
    }

    /**
     * Execute the HTTP request to OneSignal.
     */
    protected function sendRequest(array $payload)
    {
        if (!$this->appId || !$this->restApiKey) {
            Log::error('OneSignal credentials missing.');
            return ['status' => false, 'error' => 'OneSignal credentials missing'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->restApiKey,
                'Content-Type' => 'application/json',
                'accept' => 'application/json'
            ])->post($this->apiUrl, $payload);

            if ($response->successful()) {
                Log::info('OneSignal Notification Sent', ['response' => $response->json()]);
                return ['status' => true, 'data' => $response->json()];
            } else {
                Log::error('OneSignal Error', ['status' => $response->status(), 'body' => $response->body()]);
                return ['status' => false, 'error' => $response->body()];
            }
        } catch (\Exception $e) {
            Log::error('OneSignal Exception: ' . $e->getMessage());
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }
}
