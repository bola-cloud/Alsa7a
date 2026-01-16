<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

echo "\n--- OneSignal Configuration Check ---\n";
$config = Config::get('services.onesignal');

if (empty($config['app_id']) || empty($config['rest_api_key'])) {
    echo "❌ Missing Connection Configuration (App ID or Rest API Key).\n";
    print_r($config);
    exit(1);
}

echo "App ID: " . $config['app_id'] . "\n";
echo "Rest API Key: " . substr($config['rest_api_key'] ?? '', 0, 10) . "...\n";
echo "Channel Key: " . ($config['channel_key'] ?? 'N/A') . "\n";

echo "\n--- Testing API Connection (Sending Test Notification) ---\n";
echo "Attempting to send a notification to a dummy player ID to verify credentials...\n";

// We use a dummy UUID. If credentials are correct, OneSignal implies success or specific validation error.
// If credentials are bad, we get 401/403.
$response = Http::withHeaders([
    'Authorization' => 'Basic ' . $config['rest_api_key'],
    'Content-Type' => 'application/json',
    'accept' => 'application/json'
])->post('https://onesignal.com/api/v1/notifications', [
            'app_id' => $config['app_id'],
            'include_player_ids' => ['00000000-0000-0000-0000-000000000000'], // Dummy UUID
            'contents' => ['en' => 'Test Notification from Alsa7a Debug Script'],
            'headings' => ['en' => 'Connection Test'],
        ]);

echo "\nResponse Status: " . $response->status() . "\n";
echo "Response Body: " . $response->body() . "\n";

$json = $response->json();

if ($response->successful()) {
    echo "\n✅ [SUCCESS] API accepted the request. Credentials are valid.\n";
    if (isset($json['id'])) {
        echo "Notification ID generated: " . $json['id'] . "\n";
    }
} elseif ($response->status() === 400 && isset($json['errors'])) {
    // 400 Bad Request usually means credentials matched but data (like player ID) was invalid
    echo "\n✅ [SUCCESS] Credentials appear valid, but request data was rejected (expected for dummy ID).\n";
    print_r($json['errors']);
} else {
    echo "\n❌ [FAIL] API request failed. Check your credentials.\n";
}

echo "\n";
