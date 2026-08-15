<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Get my own calendar events.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $events = $this->filteredQuery($request, $user->id)->get();

        return response()->json([
            'status' => true,
            'data' => $events,
            'message' => 'Calendar events retrieved successfully.'
        ]);
    }

    /**
     * Get another user's calendar. Public by design — the whole point of the
     * feature is that anyone visiting a profile can see that player's
     * upcoming matches/events and show up to watch.
     *
     * Deliberately NOT country-scoped: the caller is asking for one specific
     * person's calendar, so filtering it by the viewer's country would hide
     * events for no reason (unlike feed/marketplace listings, which are
     * browse-style and country-scoped).
     */
    public function userCalendar(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $events = $this->filteredQuery($request, $user->id)->get();

        return response()->json([
            'status' => true,
            'data' => $events,
            'message' => 'Calendar events retrieved successfully.'
        ]);
    }

    /**
     * Shared query builder for both the own-calendar and public-calendar
     * endpoints, so filters behave identically on each.
     *
     * Supported filters (all optional):
     *   month + year  -> that exact month
     *   month only    -> that month of the current year
     *   year only     -> the whole year
     *   filter=upcoming -> from now on (the common "what's next" case)
     *   filter=past     -> already happened, newest first
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function filteredQuery(Request $request, $userId)
    {
        $query = UserEvent::where('user_id', $userId);

        if ($request->filled('month')) {
            $query->whereMonth('event_date', $request->month)
                  ->whereYear('event_date', $request->filled('year') ? $request->year : now()->year);
        } elseif ($request->filled('year')) {
            $query->whereYear('event_date', $request->year);
        }

        $filter = $request->input('filter');

        if ($filter === 'upcoming') {
            return $query->where('event_date', '>=', now())->orderBy('event_date', 'asc');
        }

        if ($filter === 'past') {
            return $query->where('event_date', '<', now())->orderBy('event_date', 'desc');
        }

        return $query->orderBy('event_date', 'asc');
    }

    /**
     * Create User Event
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            // Map pin. Both halves or neither - half a coordinate is useless.
            'latitude' => 'nullable|numeric|between:-90,90|required_with:longitude',
            'longitude' => 'nullable|numeric|between:-180,180|required_with:latitude',
            // Written address, always optional.
            'address' => 'nullable|string|max:255',
            'event_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $event = UserEvent::create([
            'user_id' => $user->id,
            'country_id' => $user->country_id,
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'event_date' => Carbon::parse($request->event_date),
        ]);

        return response()->json([
            'status' => true,
            'data' => $event,
            'message' => 'Event added to calendar successfully.'
        ], 201);
    }

    /**
     * Update User Event
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $event = UserEvent::where('id', $id)->where('user_id', $user->id)->first();
        if (!$event) {
            return response()->json([
                'status' => false,
                'message' => 'Event not found or unauthorized.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            // Map pin. Both halves or neither - half a coordinate is useless.
            'latitude' => 'nullable|numeric|between:-90,90|required_with:longitude',
            'longitude' => 'nullable|numeric|between:-180,180|required_with:latitude',
            // Written address, always optional.
            'address' => 'nullable|string|max:255',
            'event_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'event_date' => Carbon::parse($request->event_date),
        ]);

        return response()->json([
            'status' => true,
            'data' => $event,
            'message' => 'Event updated successfully.'
        ]);
    }

    /**
     * Delete User Event
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $event = UserEvent::where('id', $id)->where('user_id', $user->id)->first();
        if (!$event) {
            return response()->json([
                'status' => false,
                'message' => 'Event not found or unauthorized.'
            ], 404);
        }

        $event->delete();

        return response()->json([
            'status' => true,
            'message' => 'Event deleted successfully.'
        ]);
    }
}
