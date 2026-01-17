<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    protected $appId;
    protected $restApiKey;
    protected $apiUrl = 'https://onesignal.com/api/v1/notifications';

    public function __construct()
    {
        $this->appId = config('services.onesignal.app_id');
        $this->restApiKey = config('services.onesignal.rest_api_key');
    }

    /**
     * Send a notification to specific players (users).
     *
     * @param array $playerIds Array of OneSignal Player IDs (UUIDs).
     * @param string $title Title of the notification.
     * @param string $message Body of the notification.
     * @param array|null $data Additional data payload.
     * @return array
     */
    public function sendToPlayers(array $playerIds, string $title, string $message, ?array $data = null)
    {
        $payload = [
            'app_id' => $this->appId,
            'include_player_ids' => $playerIds,
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
        ];

        if ($data) {
            $payload['data'] = $data;
        }

        return $this->sendRequest($payload);
    }

    /**
     * Send a notification to ALL users (Broadcast).
     *
     * @param string $title Title of the notification.
     * @param string $message Body of the notification.
     * @param array|null $data Additional data payload.
     * @return array
     */
    public function sendBroadcast(string $title, string $message, ?array $data = null)
    {
        $payload = [
            'app_id' => $this->appId,
            'included_segments' => ['All'],
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
        ];

        if ($data) {
            $payload['data'] = $data;
        }

        return $this->sendRequest($payload);
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
