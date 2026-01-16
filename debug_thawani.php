<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "--- Thawani Diagnostic ---\n";

// 1. Check Config
$baseUrl = config('services.thawani.base_url');
$secret = config('services.thawani.secret_key');
$pub = config('services.thawani.publishable_key');
$mode = config('services.thawani.mode');
$payUrl = config('services.thawani.pay_url');

echo "Base URL: " . ($baseUrl ?: 'MISSING') . "\n";
echo "Mode: " . ($mode ?: 'MISSING') . "\n";
echo "Pay URL: " . ($payUrl ?: 'MISSING') . "\n";
echo "Secret Key: " . ($secret ? substr($secret, 0, 5) . '...' . substr($secret, -4) : 'MISSING') . "\n";
echo "Publishable Key: " . ($pub ? substr($pub, 0, 5) . '...' . substr($pub, -4) : 'MISSING') . "\n";

echo "\n--- Raw Env Check ---\n";
echo "THAWANI_TEST_SECRET_KEY: " . (getenv('THAWANI_TEST_SECRET_KEY') ? 'EXISTS' : 'NOT FOUND') . "\n";
echo "THAWANI_TEST_PAY_URL: " . (getenv('THAWANI_TEST_PAY_URL') ? 'EXISTS' : 'NOT FOUND') . "\n";
echo "Note: If Raw Env 'EXISTS' but Config is 'MISSING', you need to git pull + config:cache.\n";

// 2. Test Connection
echo "\n--- Testing Connection ---\n";
if (!$baseUrl || !$secret) {
    echo "Cannot test connection: Missing Config.\n";
    exit;
}

// Normalize URL logic (mimic ThawaniService)
$endpoint = str_ends_with($baseUrl, '/api/v1') ? '/checkout/session' : '/api/v1/checkout/session';
$endpoint = str_ends_with($baseUrl, '/api/v1/') ? 'checkout/session' : $endpoint; // Handle trailing slash
$fullUrl = rtrim($baseUrl, '/') . ($endpoint[0] === '/' ? '' : '/') . ltrim($endpoint, '/');

// Fix: if logic above duplicated /api/v1, clean it up manually for specific case
if (strpos($baseUrl, '/api/v1') !== false) {
    // Base URL has /api/v1, so we just want /checkout/session attached
    $fullUrl = rtrim($baseUrl, '/') . '/checkout/session';
} else {
    $fullUrl = rtrim($baseUrl, '/') . '/api/v1/checkout/session';
}

echo "Target Endpoint: $fullUrl\n";

try {
    $client = new \GuzzleHttp\Client();
    $data = [
        'client_reference_id' => 'DEBUG' . time(),
        'mode' => 'payment',
        'products' => [
            [
                'name' => 'Debug Item',
                'quantity' => 1,
                'unit_amount' => 100,
            ]
        ],
        'success_url' => 'https://example.com/success',
        'cancel_url' => 'https://example.com/cancel',
    ];

    echo "Sending Request...\n";

    $response = $client->post($fullUrl, [
        'headers' => [
            'thawani-api-key' => $secret,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'json' => $data,
        'http_errors' => false // Don't throw exception on 4xx/5xx
    ]);

    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Body: " . $response->getBody() . "\n";

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
