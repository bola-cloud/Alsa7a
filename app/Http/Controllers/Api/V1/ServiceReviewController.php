<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Notifications\RatingNotification;

class ServiceReviewController extends Controller
{
    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request, $serviceId)
    {
        $service = Service::where('is_active', true)->find($serviceId);

        if (!$service) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found'
            ], 404);
        }

        // Validate
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check availability logic (e.g. user must have booked service)
        // For now, allow open reviews or simple check
        // Check if user already reviewed?
        $existingReview = ServiceReview::where('service_id', $service->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'status' => false,
                'message' => 'You have already reviewed this service'
            ], 400);
        }

        $review = ServiceReview::create([
            'service_id' => $service->id,
            'user_id' => $request->user()->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        // Notify Service Provider
        try {
            if ($service->provider) {
                $service->provider->notify(new RatingNotification($review->load('reviewer')));
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return response()->json([
            'status' => true,
            'message' => 'Review submitted successfully',
            'data' => $review
        ], 201);
    }
}
