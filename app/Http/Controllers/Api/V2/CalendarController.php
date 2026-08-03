<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\UserEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Get User Events
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = UserEvent::where('user_id', $user->id);

        // Optional filtering by month/year
        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('event_date', $request->month)
                  ->whereYear('event_date', $request->year);
        }

        $events = $query->orderBy('event_date', 'asc')->get();

        return response()->json([
            'status' => true,
            'data' => $events,
            'message' => 'Calendar events retrieved successfully.'
        ]);
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
            'event_date' => Carbon::parse($request->event_date),
        ]);

        return response()->json([
            'status' => true,
            'data' => $event,
            'message' => 'Event added to calendar successfully.'
        ]);
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
