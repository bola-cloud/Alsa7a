<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "\n--- Generating Test Payment Link ---\n";

// Configuration
$baseUrl = config('services.thawani.base_url');
$secret = config('services.thawani.secret_key');
$publishableKey = config('services.thawani.publishable_key');
$payUrlBase = config('services.thawani.pay_url');

if (!$baseUrl || !$secret || !$publishableKey || !$payUrlBase) {
    die("Error: Missing Thawani Configuration in .env\n");
}

// Construct API URL
if (strpos($baseUrl, '/api/v1') !== false) {
    $apiUrl = rtrim($baseUrl, '/') . '/checkout/session';
} else {
    $apiUrl = rtrim($baseUrl, '/') . '/api/v1/checkout/session';
}

echo "API Endpoint: $apiUrl\n";

// Client
$client = new \GuzzleHttp\Client();

// Data
$data = [
    'client_reference_id' => 'TEST-' . time(),
    'mode' => 'payment',
    'products' => [
        [
            'name' => 'Manual Test Item',
            'quantity' => 1,
            'unit_amount' => 100, // 0.100 OMR
        ]
    ],
    // Point to the ACTUAL controllers we just fixed
    'success_url' => 'https://saha.wasl-x.com/payment/success',
    'cancel_url' => 'https://saha.wasl-x.com/payment/cancel',
    'metadata' => [
        'test_type' => 'manual_user_verification',
        'generated_by' => 'script'
    ]
];

try {
    $response = $client->post($apiUrl, [
        'headers' => [
            'thawani-api-key' => $secret,
            'Content-Type' => 'application/json',
        ],
        'json' => $data
    ]);

    $body = json_decode($response->getBody(), true);

    if (isset($body['data']['session_id'])) {
        $sessionId = $body['data']['session_id'];

        // Construct User Redirect URL
        // https://uatcheckout.thawani.om/pay/{session_id}?key={publishable_key}
        $payUrl = rtrim($payUrlBase, '/') . '/' . $sessionId . '?key=' . $publishableKey;

        echo "\nSUCCESS! Session Created.\n";
        echo "--------------------------------------------------\n";
        echo "CLICK THIS LINK TO PAY:\n";
        echo "$payUrl\n";
        echo "--------------------------------------------------\n";
        echo "\nAfter paying, you will be redirected to the success page.\n";
        echo "Run 'php filter_logs.php' afterwards to see the webhook/success logs.\n";

        // Also create a dummy transaction in DB so the success page has something to update
        // We need a dummy user. User ID 1 is usually safe.
        echo "\nCreating tracking Transaction in DB...\n";
        try {
            \App\Models\Transaction::create([
                'user_id' => 1, // Assuming admin/test user exists
                'amount' => 0.100,
                'status' => 'pending',
                'payment_method' => 'thawani',
                'transaction_reference' => $sessionId,
                'gateway_response' => ['note' => 'Manual Test Script']
            ]);
            echo "Transaction created for session $sessionId.\n";
        } catch (\Exception $e) {
            echo "Warning: Could not create local Transaction: " . $e->getMessage() . "\n";
        }

    } else {
        echo "Error: API returned success but no session_id.\n";
        print_r($body);
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if ($e instanceof \GuzzleHttp\Exception\ClientException) {
        echo "Response: " . $e->getResponse()->getBody() . "\n";
    }
}
