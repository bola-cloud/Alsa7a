<?php

use App\Models\User;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "--- Testing /my-bookings ---\n";

// 1. Get/Create User
$user = User::find(5);
if (!$user) {
    echo "User 5 not found, picking first user.\n";
    $user = User::first();
}
echo "Using User: " . $user->id . "\n";

// 2. Ensure an Event Exists
$event = Event::first();
if (!$event) {
    echo "No events found. Cannot test.\n";
    exit;
}

// 3. Create a Dummy Booking
try {
    $booking = Booking::create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'ticket_number' => 'TEST-' . time(),
        'ticket_type' => 'regular',
        'seats' => 1,
        'price_paid' => 10.00,
        'status' => 'confirmed',
        'name' => 'Tester',
        'email' => 'test@example.com',
        'phone' => '12345678',
        'payment_meta' => json_encode(['method' => 'manual'])
    ]);
    echo "Created Dummy Booking ID: " . $booking->id . "\n";
} catch (\Exception $e) {
    echo "Failed to create booking: " . $e->getMessage() . "\n";
    exit;
}

// 4. Test API
echo "\nCalling API...\n";
$request = Illuminate\Http\Request::create('/api/v1/my-bookings', 'GET');
$request->headers->set('Accept', 'application/json');
$request->setUserResolver(function () use ($user) {
    return $user;
});

// Since running via console, we invoke controller directly or via kernel?
// Easiest is to manually instantiate controller since route matches
$controller = new \App\Http\Controllers\Api\V1\EventBookingController();
$response = $controller->index($request);

echo "Status: " . $response->getStatusCode() . "\n";
$content = json_decode($response->getContent(), true);

if ($response->getStatusCode() === 200 && $content['status'] === true) {
    // Check if our booking is there
    $found = false;
    foreach ($content['data']['data'] as $b) {
        if ($b['id'] == $booking->id) {
            $found = true;
            break;
        }
    }

    if ($found) {
        echo "SUCCESS: Found created booking in response.\n";
        print_r($content['data']['data'][0]);
    } else {
        echo "FAILURE: Booking created but not found in list.\n";
        print_r($content);
    }

} else {
    echo "FAILURE: API returned error.\n";
    print_r($content);
}

// Cleanup (optional, keeping for debug might be better)
// $booking->delete();
