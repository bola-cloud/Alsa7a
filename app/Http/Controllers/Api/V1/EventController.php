<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * List Events (Public).
     * Filter: type (upcoming, trending).
     */
    public function index(Request $request)
    {
        $query = Event::with(['club', 'sport'])->where('start_at', '>=', now()->subDay()); // Show active events or recently passed? Usually >= now.

        // Type Filter
        if ($request->type === 'trending') {
            $query->orderBy('tickets_sold', 'desc')->orderBy('is_featured', 'desc');
        } else {
            // Default Upcoming
            $query->orderBy('start_at', 'asc');
        }

        // Month Filter (optional context from calendar view)
        if ($request->has('month')) {
            $query->whereMonth('start_at', $request->month);
        }

        $events = $query->paginate(10);

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
        $event = Event::with(['club', 'sport', 'media'])->find($id);

        if (!$event) {
            return response()->json(['status' => false, 'message' => 'Event not found'], 404);
        }

        // Add calculated fields or format if needed
        return response()->json([
            'status' => true,
            'data' => $event
        ]);
    }
}
