<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Validator;

class ServiceRequestController extends Controller
{
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
            ->with(['service', 'provider'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => true,
            'data' => $requests,
            'message' => 'Requests retrieved successfully'
        ]);
    }
}
