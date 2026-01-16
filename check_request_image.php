<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$app->boot();

echo "Checking My Requests Image...\n";

try {
    // 1. Setup Data
    $user = \App\Models\User::firstOrCreate(['email' => 'req_img_v2@test.com'], ['name' => 'Req Img', 'password' => bcrypt('123456')]);
    $provider = \App\Models\User::firstOrCreate(['email' => 'prov_img_v2@test.com'], ['name' => 'Prov Img', 'password' => bcrypt('123456')]);

    // Create Service with Media
    $service = \App\Models\Service::firstOrCreate(['title' => 'Image Service V2'], [
        'provider_id' => $provider->id,
        'description' => 'Desc',
        'price' => 25,
        'currency' => 'OMR',
        'is_active' => true
    ]);

    // Add media if not exists
    if ($service->media()->count() == 0) {
        $service->media()->create([
            'url' => 'storage/services/test.jpg',
            'type' => 'image'
        ]);
    }

    // Create Request
    $req = \App\Models\ServiceRequest::create([
        'requester_id' => $user->id,
        'service_id' => $service->id,
        'provider_id' => $provider->id,
        'details' => 'Image Check',
        'proposed_date' => now()->addDay(),
        'price' => 25,
        'status' => 'pending'
    ]);

    // 2. Call API
    $request = \Illuminate\Http\Request::create('/api/v1/my-requests', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user; });

    $controller = $app->make(\App\Http\Controllers\Api\V1\ServiceRequestController::class);
    $res = $controller->index($request);
    $data = $res->getData();

    if ($data->status) {
        $firstReq = $data->data->data[0] ?? null;
        if ($firstReq) {
            echo "Request Found ID: {$firstReq->id}\n";
            echo "Service ID: {$firstReq->service_id}\n";

            // Check Service Media
            $svc = \App\Models\Service::with('media')->find($firstReq->service_id);
            echo "Real DB Media Count: " . $svc->media->count() . "\n";

            // Checks result
            $serviceObj = $firstReq->service ?? null;
            if ($serviceObj) {
                echo "JSON Service Media Count: " . (count($serviceObj->media ?? [])) . "\n";
                echo "Featured Image Field: " . ($serviceObj->featured_image ?? 'MISSING') . "\n";
                if (isset($serviceObj->featured_image)) {
                    echo "Featured Image Value: " . $serviceObj->featured_image . "\n";
                }
            } else {
                echo "Service Object Missing in JSON.\n";
            }

        } else {
            echo "No requests found.\n";
        }
    } else {
        echo "API Failed.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
