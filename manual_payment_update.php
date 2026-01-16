<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

// User's specific session ID from the chat
$sessionId = 'checkout_bG9IkloN4QR5SAUxfYC3JG1BwGJFJmhg7Ay1UyERFW9wKfPoOA';

echo "--- Manual Payment Update ---\n";
echo "Session ID: $sessionId\n";

// 1. Get Payment Data from Thawani
echo "Fetching status from Thawani...\n";
$service = new \App\Services\ThawaniService();
$paymentData = $service->getPaymentStatus($sessionId);

if (!isset($paymentData['data']['payment_status'])) {
    echo "ERROR: Could not fetch status from Thawani. (Check keys)\n";
    print_r($paymentData);
    exit;
}

$status = $paymentData['data']['payment_status'];
echo "Thawani Status: $status\n";

if ($status !== 'paid') {
    echo "Payment is not PAID yet in Thawani. Cannot update local DB.\n";
    exit;
}

// 2. Find Transaction
$txn = \App\Models\Transaction::where('transaction_reference', $sessionId)->first();

if (!$txn) {
    echo "ERROR: Transaction not found in DB for this session ID.\n";
    exit;
}

echo "Transaction Found: ID {$txn->id}, Current Status: {$txn->status}\n";

// 3. Update
if ($txn->status !== 'completed') {
    $txn->update([
        'status' => 'completed',
        'gateway_response' => ['status' => $status, 'updated_at' => now()]
    ]);
    echo "Transaction updated to COMPLETED.\n";

    if ($txn->service_request_id) {
        $req = \App\Models\ServiceRequest::find($txn->service_request_id);
        if ($req) {
            $req->update(['payment_status' => 'paid', 'status' => 'paid']);
            echo "Service Request {$req->id} updated to PAID.\n";
        }
    }
} else {
    echo "Transaction was already completed.\n";
}

echo "Done.\n";
