<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Notifications\ServiceRequested;
use App\Models\Conversation;

class ServiceRequestController extends Controller
{
    /**
     * Request a service (Booking).
     */
    public function store(Request $request, $serviceId)
    {
        $service = Service::where('is_active', true)->find($serviceId);

        if (!$service) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found or inactive'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'scheduled_at' => 'required|date|after:now',
            'message' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceRequest = ServiceRequest::create([
            'service_id' => $service->id,
            'requester_id' => $request->user()->id,
            'provider_id' => $service->provider_id, // Assuming service has provider_id
            'scheduled_at' => $request->scheduled_at,
            'price' => $service->price, // Snapshot price
            'message' => $request->message,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        // Notify Provider
        if ($serviceRequest->provider) {
            $serviceRequest->provider->notify(new ServiceRequested($serviceRequest));
        }

        return response()->json([
            'status' => true,
            'message' => 'Service requested successfully',
            'data' => $serviceRequest
        ], 201);
    }

    /**
     * Pay for a service request (Mock).
     */
    public function pay(Request $request, $id)
    {
        $serviceRequest = ServiceRequest::where('requester_id', $request->user()->id)->find($id);

        if (!$serviceRequest) {
            return response()->json(['status' => false, 'message' => 'Request not found'], 404);
        }

        if ($serviceRequest->status !== 'accepted') {
            return response()->json(['status' => false, 'message' => 'Request must be accepted before payment'], 400);
        }

        if ($serviceRequest->payment_status === 'paid') {
            return response()->json(['status' => false, 'message' => 'Already paid'], 400);
        }

        // Mock Payment Logic
        $serviceRequest->payment_status = 'paid';
        $serviceRequest->payment_transaction_id = 'TXN_' . uniqid();
        $serviceRequest->save();

        // Create Chat Conversation automatically upon payment?
        // Or create it on first message. Let's create it here to enable the chat button.
        $conversation = Conversation::firstOrCreate([
            'service_request_id' => $serviceRequest->id
        ], [
            'user_one_id' => $serviceRequest->requester_id,
            'user_two_id' => $serviceRequest->provider_id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Payment successful',
            'data' => [
                'service_request' => $serviceRequest,
                'conversation_id' => $conversation->id
            ]
        ]);
    }

    /**
     * List user's requests.
     */
    public function index(Request $request)
    {
        $requests = ServiceRequest::with(['service', 'provider'])
            ->where('requester_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => true,
            'data' => $requests,
            'message' => 'Requests retrieved successfully'
        ]);
    }
}
