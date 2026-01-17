<?php

use App\Models\Club;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Database...\n";
$clubCount = Club::count();
echo "Total Clubs: " . $clubCount . "\n";

if ($clubCount > 0) {
    echo "First Club ID: " . Club::first()->id . "\n";
}

echo "\nChecking API Route...\n";
$request = Request::create('/api/v1/clubs', 'GET');
$response = $app->handle($request);

echo "API Response Status: " . $response->getStatusCode() . "\n";
echo "API Response Body: " . substr($response->getContent(), 0, 500) . "...\n";
