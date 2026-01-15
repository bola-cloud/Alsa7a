<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$app->boot();

try {
    echo "Starting Event Features Test...\n";

    // 1. Create User
    $user = \App\Models\User::firstOrCreate(
        ['email' => 'event_tester@example.com'],
        [
            'name' => 'Event Tester',
            'password' => bcrypt('password'),
            'category_id' => 2
        ]
    );
    echo "User ID: " . $user->id . "\n";

    // 2. Create Pricing Event (Paid)
    $paidEvent = \App\Models\Event::firstOrCreate(
        ['title_en' => 'Paid Concert'],
        [
            'club_id' => 1,
            'title_ar' => 'Paid Concert AR',
            'description_en' => 'Music',
            'description_ar' => 'Music AR',
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(5)->addHours(2),
            'price' => 100,
            'capacity' => 50,
            'tickets_sold' => 0,
            'is_active' => true,
            'venue' => 'Test Venue'
        ]
    );

    // 3. Create Free Event
    $freeEvent = \App\Models\Event::firstOrCreate(
        ['title_en' => 'Free Workshop'],
        [
            'club_id' => 1,
            'title_ar' => 'Free Workshop AR',
            'description_en' => 'Learning',
            'description_ar' => 'Learning AR',
            'start_at' => now()->addDays(6),
            'end_at' => now()->addDays(6)->addHours(2),
            'price' => 0,
            'capacity' => 50,
            'tickets_sold' => 0,
            'is_active' => true,
            'venue' => 'Test Venue'
        ]
    );

    // 4. Test Booking Paid Event (Should be PENDING)
    echo "\nBooking Paid Event...\n";
    $reqPaid = \Illuminate\Http\Request::create('/api/v1/events/' . $paidEvent->id . '/book', 'POST', [
        'ticket_type' => 'Regular', // Assuming logic handles missing types by fallback to global price if generic
        'seats' => 1,
        'name' => 'Tester',
        'email' => 'event_tester@example.com',
        'phone' => '123456'
    ]);
    $reqPaid->setUserResolver(function () use ($user) {
        return $user;
    });

    // Simulate Controller Call (Manual instantiation to avoid full routing middleware complexity in CLI)
    $controller = new \App\Http\Controllers\Api\V1\EventBookingController();
    $resPaid = $controller->store($reqPaid, $paidEvent->id);
    $dataPaid = $resPaid->getData();

    echo "Status: " . ($dataPaid->status ? 'OK' : 'FAIL') . "\n";
    if (isset($dataPaid->data)) {
        echo "Booking ID: " . $dataPaid->data->id . "\n";
        echo "Booking Status: " . $dataPaid->data->status . " (Expected: pending)\n";
    } else {
        echo "Error: " . json_encode($dataPaid) . "\n";
    }

    // 5. Test Booking Free Event (Should be CONFIRMED)
    echo "\nBooking Free Event...\n";
    $reqFree = \Illuminate\Http\Request::create('/api/v1/events/' . $freeEvent->id . '/book', 'POST', [
        'ticket_type' => 'Regular',
        'seats' => 1,
        'name' => 'Tester',
        'email' => 'event_tester@example.com',
        'phone' => '123456'
    ]);
    $reqFree->setUserResolver(function () use ($user) {
        return $user;
    });

    $resFree = $controller->store($reqFree, $freeEvent->id);
    $dataFree = $resFree->getData();

    echo "Status: " . ($dataFree->status ? 'OK' : 'FAIL') . "\n";
    if (isset($dataFree->data)) {
        echo "Booking ID: " . $dataFree->data->id . "\n";
        echo "Booking Status: " . $dataFree->data->status . " (Expected: confirmed)\n";
    }

    // 6. Test Listing Bookings
    echo "\nListing Bookings...\n";
    $reqList = \Illuminate\Http\Request::create('/api/v1/my-bookings', 'GET');
    $reqList->setUserResolver(function () use ($user) {
        return $user;
    });
    $resList = $controller->index($reqList);
    $dataList = $resList->getData();

    echo "Total Bookings: " . count($dataList->data->data) . "\n";
    foreach ($dataList->data->data as $b) {
        echo "- Event: " . $b->event->title . " | Status: " . $b->status . " | Payment Status: " . ($b->payment_status ?? 'N/A') . "\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
