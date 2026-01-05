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
    /**
     * Create a new service (Protected: Provider).
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Optional: Check if user is a provider or approved
        // if (!$user->is_approved) { ... }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'sport_id' => 'required|exists:sports,id',
            'price' => 'required|numeric|min:0',
            'days_available' => 'required|array', // ['MON', 'TUE']
            'days_available.*' => 'string|in:SUN,MON,TUE,WED,THU,FRI,SAT',
            'location' => 'nullable|string|max:255',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|max:10240', // Limit 10MB per image
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $service = Service::create([
            'provider_id' => $user->id,
            'club_id' => $user->club_id, // If associated with a club
            'sport_id' => $request->sport_id,
            'title' => $request->title,
            'slug' => \Illuminate\Support\Str::slug($request->title . '-' . uniqid()),
            'description' => $request->description,
            'location' => $request->location,
            'days_available' => $request->days_available,
            'price' => $request->price,
            'currency' => 'JOD', // Default or from settings
            'is_active' => true,
        ]);

        // Handle Gallery
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $path = $file->store('services', 'public');
                $service->media()->create([
                    'url' => 'storage/' . $path,
                    'type' => 'image', // Assuming mostly images from context, but could check mime
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Service created successfully',
            'data' => $service->load('media')
        ], 201);
    }
}
