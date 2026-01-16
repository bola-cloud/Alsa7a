<?php
// Script to filter Laravel logs for Thawani/Payment related entries
// Run this on the server: php filter_logs.php

$logFile = __DIR__ . '/storage/logs/laravel.log';

if (!file_exists($logFile)) {
    die("Error: Log file not found at $logFile\n");
}

echo "--- Reading $logFile ---\n";
echo "--- Filtering for 'Thawani' or 'Payment' ---\n\n";

$handle = fopen($logFile, "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        if (stripos($line, 'Thawani') !== false || stripos($line, 'Payment') !== false) {
            echo trim($line) . "\n";
        }
    }
    fclose($handle);
} else {
    echo "Error: Unable to open log file.\n";
}

echo "\n--- End of Logs ---\n";
