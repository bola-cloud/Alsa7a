<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        return response()->json([
            'status' => true,
            'message' => 'Service requested successfully',
            'data' => $serviceRequest
        ], 201);
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
