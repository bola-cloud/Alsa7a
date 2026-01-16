<?php

$requestId = $argv[1] ?? null;

if (!$requestId) {
    die("Usage: php trace_payment.php <service_request_id>\n");
}

$logFile = __DIR__ . '/storage/logs/laravel.log';

if (!file_exists($logFile)) {
    die("Error: Log file not found.\n");
}

echo "--- Tracing Request ID: $requestId ---\n\n";

$logs = file_get_contents($logFile);
$lines = explode("\n", $logs);

$found = false;
$txnId = null;
$sessionId = null;

// FIRST PASS: Find the Request Creation and Payment Session to get IDs
foreach ($lines as $line) {
    if (strpos($line, "Service Request Created: ID $requestId") !== false) {
        echo "[REQUEST CREATED] " . substr($line, 0, 21) . "\n";
        $found = true;
    }

    // Look for payment session
    // Since we didn't index by Request ID in the payment log before (only session ID), 
    // we might need to rely on the time or look for context.
    // BUT the new log I added *doesn't* explicitly link Request ID in the same line.

    // However, the *Payment Controller* logic finds the request ID.
    // The "Processing Payment Update" log mentions "Txn {id}".
    // The "Service Request {id} marked as paid" log explicitly mentions the ID.

    if (strpos($line, "Service Request $requestId marked as paid") !== false) {
        echo "[MARKED PAID]     " . substr($line, 0, 21) . "\n";
        $found = true;
    }
}

// SIMPLIFIED TRACE: Just grep for the ID
echo "\n--- Related Logs for Request $requestId ---\n";
foreach ($lines as $line) {
    if (strpos($line, "Service Request $requestId") !== false || strpos($line, "Service Request: $requestId") !== false) {
        echo trim($line) . "\n";
    }
}

echo "\n--- Checking for Transaction Logs ---\n";
// We need to find the transaction ID from the DB to grep efficiently, 
// OR just grep for "Service Request $requestId" in the controller logs I added.

echo "You can view full context by running: php filter_logs.php\n";
