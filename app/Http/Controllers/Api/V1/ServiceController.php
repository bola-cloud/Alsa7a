<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the services.
     * Public endpoint.
     */
    public function index(Request $request)
    {
        $query = Service::with(['provider', 'sport', 'club', 'reviews'])
            ->where('is_active', true);

        // Filter by Sport
        if ($request->has('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        // Filter by Location (simple search)
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Filter by Category (via Provider)
        if ($request->has('category_id') && $request->category_id !== 'all') {
            $query->whereHas('provider', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $services = $query->latest()->paginate(10);

        // Append average rating to each service
        $services->getCollection()->transform(function ($service) {
            $service->average_rating = $service->reviews->avg('rating') ?? 0;
            return $service;
        });

        return response()->json([
            'status' => true,
            'data' => $services,
            'message' => 'Services retrieved successfully'
        ]);
    }

    /**
     * Display the specified service.
     * Public endpoint.
     */
    public function show($id)
    {
        $service = Service::with(['provider', 'sport', 'club', 'reviews.user'])
            ->where('is_active', true)
            ->find($id);

        if (!$service) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found'
            ], 404);
        }

        $service->average_rating = $service->reviews->avg('rating') ?? 0;

        return response()->json([
            'status' => true,
            'data' => $service,
            'message' => 'Service details retrieved successfully'
        ]);
    }
}
