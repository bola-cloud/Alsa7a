<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transaction;
use App\Services\ThawaniService;
use App\Models\ServiceRequest;
use App\Models\Booking;

// Check for session ID argument
if ($argc < 2) {
    echo "Usage: php manual_payment_update.php <session_id>\n";
    // Fallback for testing if you want to hardcode
    // $sessionId = 'checkout_...';
    exit(1);
}

$sessionId = $argv[1];

echo "--- Manual Payment Update ---\n";
echo "Session ID: " . $sessionId . "\n";

$thawaniService = new ThawaniService();

echo "Fetching status from Thawani...\n";

try {
    // We'll use the public getPaymentStatus to verify connectivity,
    // but we also want to inspect the raw response if possible.
    // Since getPaymentStatus only returns a string, let's reflect into the service 
    // or just assume if it works, it works, but here we want to debug "unpaid".

    // Let's manually trigger the underlying client request to see the full body
    $client = new \GuzzleHttp\Client();
    $secretKey = config('services.thawani.secret_key');
    $baseUrl = config('services.thawani.base_url');

    // Construct the URL manually to be sure
    // Thawani API: GET /api/v1/checkout/session/{session_id}
    // Remove /api/v1 from baseUrl if it exists, to avoid duplication if we want to be safe, 
    // OR just use it as is if we know what it is.
    // The previous error was: https://uatcheckout.thawani.om/api/v1/api/v1/...

    // Fix: Clean the base URL to NOT have /api/v1 at the end if we are appending it, 
    // OR don't append it if it's there.
    if (strpos($baseUrl, '/api/v1') !== false) {
        $url = $baseUrl . '/checkout/session/' . $sessionId;
    } else {
        $url = $baseUrl . '/api/v1/checkout/session/' . $sessionId;
    }

    echo "Requesting: $url\n";

    $response = $client->get($url, [
        'headers' => [
            'thawani-api-key' => $secretKey,
            'Content-Type' => 'application/json',
        ]
    ]);

    $body = json_decode($response->getBody(), true);

    echo "--- FULL THAWANI RESPONSE ---\n";
    print_r($body);
    echo "-----------------------------\n";

    $status = $body['data']['payment_status'] ?? 'unknown';

    echo "Thawani Status: " . $status . "\n";

    if ($status === 'paid') {
        echo "Payment is PAID. Updating local DB...\n";

        // Create controller instance with dependency
        $controller = new \App\Http\Controllers\Api\V1\PaymentController($thawaniService);

        // Use reflection to call the protected method processPaymentUpdate/checkStatus logic
        // Or simpler: just replicate the update logic here.

        $transaction = Transaction::where('transaction_reference', $sessionId)->first();

        if ($transaction) {
            echo "Transaction found: ID " . $transaction->id . "\n";

            if ($transaction->status === 'paid') {
                echo "Transaction is ALREADY paid in DB.\n";
            } else {
                $transaction->update([
                    'status' => 'paid',
                    'gateway_response' => json_encode($body)
                ]);
                echo "Transaction status updated to 'paid'.\n";

                // Update related models
                if ($transaction->service_request_id) {
                    $sr = ServiceRequest::find($transaction->service_request_id);
                    if ($sr) {
                        $sr->update(['payment_status' => 'paid', 'status' => 'paid']);
                        echo "ServiceRequest updated.\n";
                    }
                }

                if ($transaction->booking_id) {
                    $bk = Booking::find($transaction->booking_id);
                    if ($bk) {
                        $bk->update(['status' => 'confirmed', 'payment_status' => 'paid']);
                        echo "Booking updated.\n";
                    }
                }
            }
        } else {
            echo "Error: Transaction not found in DB for this session ID.\n";
        }

    } else {
        echo "Payment is not PAID yet in Thawani. Cannot update local DB.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
