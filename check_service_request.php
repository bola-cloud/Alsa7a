<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// We need to set up the application context
$app->boot();

// Create logic
try {
    echo "Starting test...\n";

    // 1. Get or Create a Provider
    $provider = \App\Models\User::firstOrCreate(
        ['email' => 'provider_test@example.com'],
        [
            'name' => 'Test Provider',
            'password' => bcrypt('password'),
            'category_id' => 1 // Assuming 1 exists
        ]
    );
    echo "Provider ID: " . $provider->id . "\n";

    // 2. Get or Create a Service
    $service = \App\Models\Service::firstOrCreate(
        ['title' => 'Test Service for Request'],
        [
            'provider_id' => $provider->id,
            'description' => 'Testing API',
            'sport_id' => 1, // Assuming 1 exists
            'price' => 50,
            'days_available' => ['SUN'],
            'is_active' => true
        ]
    );
    echo "Service ID: " . $service->id . "\n";

    // 3. Get or Create a User (Requester)
    $requester = \App\Models\User::firstOrCreate(
        ['email' => 'requester_test@example.com'],
        [
            'name' => 'Test Requester',
            'password' => bcrypt('password'),
            'category_id' => 2 // Assuming 2 exists
        ]
    );
    echo "Requester ID: " . $requester->id . "\n";

    // 4. Simulate the Request Logic directly (to verify Controller logic via Model)
    // Or simpler: Just create the ServiceRequest model directly as the Controller does

    echo "Attempting to create Service Request...\n";

    $requestData = [
        'service_id' => $service->id,
        'requester_id' => $requester->id,
        'provider_id' => $service->provider_id,
        'status' => 'pending',
        'scheduled_at' => now()->addDay(),
        'message' => 'I want to book this.',
        'price' => $service->price,
        'payment_status' => 'pending',
    ];

    $serviceRequest = \App\Models\ServiceRequest::create($requestData);

    echo "Service Request Created Successfully!\n";
    echo "Request ID: " . $serviceRequest->id . "\n";
    echo "Status: " . $serviceRequest->status . "\n";
    echo "Scheduled At: " . $serviceRequest->scheduled_at . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
