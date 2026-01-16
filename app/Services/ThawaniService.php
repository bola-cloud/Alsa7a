<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ThawaniService
{
    protected $baseUrl;
    protected $secretKey;
    protected $publishableKey;

    public function __construct()
    {
        $this->baseUrl = config('services.thawani.base_url', 'https://uatcheckout.thawani.om');
        $this->secretKey = config('services.thawani.secret_key', 'rRQ26GcsZzoLp0MKWtCKkf59T57Ver'); // Sandbox Default
        $this->publishableKey = config('services.thawani.publishable_key', 'HGvTMLDssJghr9tlQS6AgHe0GN5X9n'); // Sandbox Default
    }

    /**
     * Create a Checkout Session
     */
    public function createCheckoutSession($data)
    {
        $response = Http::withHeaders([
            'thawani-api-key' => $this->secretKey
        ])->post("{$this->baseUrl}/api/v1/checkout/session", $data);

        return $response->json();
    }

    /**
     * Get Payment Status by Session ID
     */
    public function getPaymentStatus($sessionId)
    {
        $response = Http::withHeaders([
            'thawani-api-key' => $this->secretKey
        ])->get("{$this->baseUrl}/api/v1/checkout/session/{$sessionId}");

        return $response->json();
    }
}
