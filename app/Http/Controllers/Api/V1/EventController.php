<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Traits\FormatsProfileData;

class EventController extends Controller
{
    use FormatsProfileData;
    /**
     * List Events (Public).
     * Filter: type (upcoming, trending).
     */
    public function index(Request $request)
    {
        $query = Event::with(['club.owner.subscription', 'club.owner.category', 'sport']);

        // 1. Time Filtering (Support both 'time' and legacy 'type')
        $time = $request->input('time', $request->input('type'));
        
        if ($time === 'upcoming') {
            $query->where('start_at', '>', now());
        } elseif ($time === 'past') {
            $query->where('start_at', '<', now());
        } else {
            // Default: current and future
            $query->where('start_at', '>=', now()->subDay());
        }

        // 2. Sorting (Support both 'sort' and 'type')
        $sort = $request->input('sort', $request->input('type'));

        if ($sort === 'trending') {
            $query->orderBy('tickets_sold', 'desc')->orderBy('is_featured', 'desc');
        } elseif ($time === 'past') {
            // Past events: newest first by default
            $query->orderBy('start_at', 'desc');
        } else {
            // Upcoming events: closest first by default
            $query->orderBy('start_at', 'asc');
        }

        // 3. Month Filter (optional)
        if ($request->has('month')) {
            $query->whereMonth('start_at', $request->month);
        }

        $events = $query->paginate(10);

        $currentUser = $request->user('sanctum');
        $events->getCollection()->transform(function ($event) use ($currentUser) {
            if ($event->club && $event->club->owner) {
                $event->club_profile = $this->getProfileData($event->club->owner, false, $currentUser);
            } else {
                $event->club_profile = null;
            }
            return $event;
        });

        return response()->json([
            'status' => true,
            'data' => $events
        ]);
    }

    /**
     * Show Event Details (Public).
     */
    public function show($id)
    {
        $event = Event::with(['club.owner.subscription', 'club.owner.category', 'sport', 'media'])->find($id);

        if (!$event) {
            return response()->json(['status' => false, 'message' => 'Event not found'], 404);
        }

        if ($event->club && $event->club->owner) {
            $event->club_profile = $this->getProfileData($event->club->owner, false, request()->user('sanctum'));
        }

        // Add calculated fields or format if needed
        return response()->json([
            'status' => true,
            'data' => $event
        ]);
    }
}
