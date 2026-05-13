<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\ImageService;
use Illuminate\Support\Facades\Validator;

class ClubEventController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * List events owned by the club.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $club = $user->ownedClub;

        if (!$club) {
            return response()->json(['status' => false, 'message' => 'User does not own a club'], 403);
        }

        $events = Event::where('club_id', $club->id)->latest()->paginate(10);

        return response()->json([
            'status' => true,
            'data' => $events
        ]);
    }

    /**
     * Store a new event from the club.
     * Status is set to 'pending' by default.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $club = $user->ownedClub;

        if (!$club) {
            return response()->json(['status' => false, 'message' => 'User does not own a club'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title_en' => 'required|string|max:255',
            'title_ar' => 'required|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'sport_id' => 'required|exists:sports,id',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after:start_at',
            'venue' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['club_id'] = $club->id;
        $data['status'] = 'pending'; // Explicitly set to pending
        $data['slug'] = Str::slug($data['title_en']) . '-' . uniqid();

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->imageService->upload($request->file('featured_image'), 'events');
        }

        $event = Event::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Event created and pending approval',
            'data' => $event
        ], 201);
    }
}
