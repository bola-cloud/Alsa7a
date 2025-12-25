<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderRequestController extends Controller
{
    /**
     * List incoming requests for the provider.
     */
    public function index(Request $request)
    {
        // Get requests where the authenticated user is the provider
        $requests = ServiceRequest::with(['service', 'requester'])
            ->where('provider_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => true,
            'data' => $requests,
            'message' => 'Incoming requests retrieved successfully'
        ]);
    }

    /**
     * Update request status (Accept/Reject).
     */
    public function updateStatus(Request $request, $id)
    {
        $serviceRequest = ServiceRequest::where('provider_id', $request->user()->id)
            ->find($id);

        if (!$serviceRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Request not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:accepted,rejected,completed,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceRequest->status = $request->status;
        $serviceRequest->save();

        return response()->json([
            'status' => true,
            'message' => 'Request status updated to ' . $request->status,
            'data' => $serviceRequest
        ]);
    }
}
