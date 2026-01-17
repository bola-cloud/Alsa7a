<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Validator;

use App\Services\OneSignalService;

class ServiceRequestController extends Controller
{
    protected $oneSignal;

    public function __construct(OneSignalService $oneSignal)
    {
        $this->oneSignal = $oneSignal;
    }

    /**
     * Book a service (Create a Request).
     * POST /services/{id}/request
     */
    public function store(Request $request, $id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['status' => false, 'message' => 'Service not found'], 404);
        }

        // Validate
        $validator = Validator::make($request->all(), [
            'scheduled_at' => 'required|date|after:now',
            'message' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Prevent booking own service
        if ($service->provider_id == $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'You cannot book your own service'], 400);
        }

        // Create Request
        $serviceRequest = ServiceRequest::create([
            'service_id' => $service->id,
            'requester_id' => $request->user()->id,
            'provider_id' => $service->provider_id,
            'status' => 'pending',
            'scheduled_at' => $request->scheduled_at,
            'message' => $request->message,
            'price' => $service->price, // Snapshot price
            'payment_status' => 'pending',
        ]);

        \Illuminate\Support\Facades\Log::info("Service Request Created: ID {$serviceRequest->id} by User {$request->user()->id}");

        // Notify Provider
        $provider = $service->provider; // Assuming relation exists on Service model
        if ($provider && !empty($provider->onesignal_subscription['id'])) {
            $playerId = $provider->onesignal_subscription['id'];
            $this->oneSignal->sendToPlayers(
                [$playerId],
                'New Service Request',
                "{$request->user()->name} has requested your service: {$service->title}",
                ['request_id' => $serviceRequest->id, 'type' => 'new_request']
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'Service requested successfully',
            'data' => $serviceRequest
        ], 201);
    }

    /**
     * List my requests.
     * GET /my-requests
     */
    public function index(Request $request)
    {
        $requests = ServiceRequest::where('requester_id', $request->user()->id)
            ->with(['service.media', 'provider'])
            ->latest()
            ->paginate(10);

        // Transform to include full image URL
        $requests->getCollection()->transform(function ($req) {
            if ($req->service && $req->service->media->isNotEmpty()) {
                $firstMedia = $req->service->media->first();
                $image = $firstMedia->file_path ?? $firstMedia->image;
                if ($image) {
                    $req->service->featured_image = preg_match('#^https?://#i', $image) ? $image : asset('storage/' . $image);
                }
            } else {
                $req->service->featured_image = null;
            }
            return $req;
        });

        return response()->json([
            'status' => true,
            'data' => $requests,
            'message' => 'Requests retrieved successfully'
        ]);
    }
}
