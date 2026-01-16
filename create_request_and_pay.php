<?php

use App\Models\User;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "\n--- Generating Service Request & Payment Link ---\n";

// 1. Setup Data
$user = User::first() ?? User::factory()->create();
$provider = User::where('id', '!=', $user->id)->first() ?? User::factory()->create();
$service = Service::first();

if (!$service) {
    // Create dummy service if none exists
    $service = Service::create([
        'provider_id' => $provider->id,
        'title_en' => 'Test Service',
        'title_ar' => 'Test Service AR',
        'price' => 10,
        'is_active' => true,
        'category_id' => 1 // Assuming category 1 exists, otherwise defaults might be needed
    ]);
}

// 2. Create Service Request
$req = ServiceRequest::create([
    'service_id' => $service->id,
    'requester_id' => $user->id,
    'provider_id' => $provider->id,
    'status' => 'accepted', // Must be accepted to pay
    'price' => 10,
    'payment_status' => 'pending',
    'scheduled_at' => now()->addDays(1),
]);

echo "Created Service Request ID: {$req->id}\n";
echo "Price: {$req->price} OMR\n";

// 3. Prepare Thawani Payload
$clientReference = 'TXN-' . uniqid();
$amountInBaisa = (int) ($req->price * 1000);

$baseUrl = config('services.thawani.base_url');
$secret = config('services.thawani.secret_key');
$publishableKey = config('services.thawani.publishable_key');
$payUrlBase = config('services.thawani.pay_url');

if (!$baseUrl || !$secret) {
    die("Error: Thawani config missing.\n");
}

// Construct API URL
if (strpos($baseUrl, '/api/v1') !== false) {
    $apiUrl = rtrim($baseUrl, '/') . '/checkout/session';
} else {
    $apiUrl = rtrim($baseUrl, '/') . '/api/v1/checkout/session';
}

$client = new \GuzzleHttp\Client();
$data = [
    'client_reference_id' => $clientReference,
    'mode' => 'payment',
    'products' => [
        [
            'name' => 'Service Request ' . $req->id,
            'quantity' => 1,
            'unit_amount' => $amountInBaisa,
        ]
    ],
    // Essential: Add ref parameter so our controller can find it if session_id is missing
    'success_url' => 'https://saha.wasl-x.com/payment/success?ref=' . $clientReference,
    'cancel_url' => 'https://saha.wasl-x.com/payment/cancel',
    'metadata' => [
        'service_request_id' => $req->id,
        'user_id' => $user->id
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

        // 4. Create Transaction Linked to Request
        $txn = Transaction::create([
            'user_id' => $user->id,
            'service_request_id' => $req->id,
            'booking_id' => null,
            'amount' => $req->price,
            'status' => 'pending',
            'payment_method' => 'thawani',
            'transaction_reference' => $sessionId,
            'gateway_response' => ['client_reference_id' => $clientReference]
        ]);

        $payUrl = rtrim($payUrlBase, '/') . '/' . $sessionId . '?key=' . $publishableKey;

        echo "\nSUCCESS! Transaction Created (ID: {$txn->id}).\n";
        echo "--------------------------------------------------\n";
        echo "CLICK THIS LINK TO PAY:\n";
        echo "$payUrl\n";
        echo "--------------------------------------------------\n";

    } else {
        echo "Error: Thawani API did not return session_id.\n";
        print_r($body);
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
