<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Traits\FormatsProfileData;

class ServiceController extends Controller
{
    use FormatsProfileData;
    /**
     * Display a listing of the services.
     * Public endpoint.
     */
    public function index(Request $request)
    {
        $query = Service::with([
            'provider.category.parentCategory',
            'provider.club',
            'provider.ownedClub',
            'sport',
            'club',
            'reviews',
            'media'
        ])
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

        // Filter by Provider ID. Asking for one provider's services is
        // direct access (their profile tab), not browsing, so drop the
        // country filter for this case only.
        if ($request->has('provider_id') && $request->provider_id != null) {
            $query->withoutGlobalScope(\App\Scopes\CountryScope::class)
                  ->where('provider_id', $request->provider_id);
        }

        // Filter by Type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $services = $query->latest()->paginate(10);

        // Append average rating to each service
        $currentUser = $request->user('sanctum');
        // Unique users (providers) processing
        $providersToProcess = collect();
        foreach ($services as $service) {
            if ($service->provider) $providersToProcess->put($service->provider->id, $service->provider);
        }

        $providersToProcess->each(function ($provider) use ($currentUser) {
            if (is_object($provider)) {
                $profileData = $this->getProfileData($provider, false, $currentUser);
                foreach ($profileData as $key => $value) {
                    if (!is_array($provider->{$key})) {
                        $provider->{$key} = $value;
                    }
                }
            }
        });

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
    public function show(Request $request, $id)
    {
        // Direct access by id - country filter must not hide it.
        $service = Service::directAccess()->with([
            'provider.category.parentCategory',
            'provider.club',
            'provider.ownedClub',
            'sport',
            'club',
            'reviews.user.category',
            'reviews.user.club',
            'reviews.user.ownedClub',
            'media'
        ])
            ->where('is_active', true)
            ->find($id);

        if (!$service) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found'
            ], 404);
        }

        $service->average_rating = $service->reviews->avg('rating') ?? 0;

        $currentUser = $request->user('sanctum');

        if ($service->provider && is_object($service->provider)) {
            $profileData = $this->getProfileData($service->provider, false, $currentUser);
            foreach ($profileData as $key => $value) {
                if (!is_array($service->provider->{$key})) {
                    $service->provider->{$key} = $value;
                }
            }
        }

        // Unique reviewers processing
        $reviewersToProcess = collect();
        foreach ($service->reviews as $review) {
            if ($review->user) $reviewersToProcess->put($review->user->id, $review->user);
        }

        $reviewersToProcess->each(function ($userObj) use ($currentUser) {
            if (is_object($userObj)) {
                $userData = $this->getProfileData($userObj, false, $currentUser);
                foreach ($userData as $key => $value) {
                    if (!is_array($userObj->{$key})) {
                        $userObj->{$key} = $value;
                    }
                }
            }
        });

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

        $category = $user->category;
        $isMandatory = $category ? (bool) $category->mandatory_service_verification : false;

        if ($isMandatory || setting('mandatory_service_verification', false)) {
            if ($user->verification_status !== 'approved') {
                return response()->json(['status' => false, 'message' => 'You must verify your profile before creating a service.'], 403);
            }
        }

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
            'address' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:default,performance_experience,loan_request',
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
            'address' => $request->address,
            'days_available' => $request->days_available,
            'price' => $request->price,
            'currency' => 'OMR', // Standardized to OMR
            'is_active' => true,
            'type' => $request->type ?? 'default',
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


    /**
     * Update a service.
     */
    public function update(Request $request, $id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['status' => false, 'message' => 'Service not found'], 404);
        }

        // Authorization: Only owner can update
        if ($service->provider_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'sport_id' => 'sometimes|exists:sports,id',
            'price' => 'sometimes|numeric|min:0',
            'days_available' => 'sometimes|array',
            'days_available.*' => 'string|in:SUN,MON,TUE,WED,THU,FRI,SAT',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'type' => 'sometimes|string|in:default,performance_experience,loan_request',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Update fields if present
        if ($request->has('title'))
            $service->title = $request->title;
        if ($request->has('description'))
            $service->description = $request->description;
        if ($request->has('sport_id'))
            $service->sport_id = $request->sport_id;
        if ($request->has('price'))
            $service->price = $request->price;
        if ($request->has('days_available'))
            $service->days_available = $request->days_available;
        if ($request->has('location'))
            $service->location = $request->location;
        if ($request->has('address'))
            $service->address = $request->address;
        if ($request->has('type'))
            $service->type = $request->type;

        $service->save();

        // Handle Gallery (Add new images)
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $path = $file->store('services', 'public');
                $service->media()->create([
                    'url' => 'storage/' . $path,
                    'type' => 'image',
                ]);
            }
        }

        // Handle deleting specific media if "deleted_media_ids" array is passed (Optional improvement)
        if ($request->has('deleted_media_ids') && is_array($request->deleted_media_ids)) {
            $service->media()->whereIn('id', $request->deleted_media_ids)->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Service updated successfully',
            'data' => $service->load('media')
        ]);
    }

    /**
     * Delete a service.
     */
    public function destroy(Request $request, $id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['status' => false, 'message' => 'Service not found'], 404);
        }

        // Authorization: Only owner can delete
        if ($service->provider_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $service->delete();

        return response()->json([
            'status' => true,
            'message' => 'Service deleted successfully'
        ]);
    }
}
