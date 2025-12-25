<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EventBookingController extends Controller
{
    /**
     * Book Event Ticket (Protected).
     */
    public function store(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['status' => false, 'message' => 'Event not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'ticket_type' => 'required|string',
            'seats' => 'integer|min:1',
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'country_code' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Validate Ticket Type and Price
        $ticketTypes = $event->ticket_types ?? [];
        $selectedType = null;
        foreach ($ticketTypes as $type) {
            if ($type['name'] === $request->ticket_type) {
                $selectedType = $type;
                break;
            }
        }

        if (!$selectedType) {
            // Fallback if no specific types defined but event has global price
            if (empty($ticketTypes) && $event->price) {
                $price = $event->price;
            } else {
                return response()->json(['status' => false, 'message' => 'Invalid ticket type'], 400);
            }
        } else {
            $price = $selectedType['price'] ?? 0;
        }

        // Check Capacity
        $requestedSeats = $request->seats ?? 1;
        if ($event->capacity && ($event->tickets_sold + $requestedSeats > $event->capacity)) {
            return response()->json(['status' => false, 'message' => 'Not enough tickets available'], 400);
        }

        try {
            DB::beginTransaction();

            $booking = Booking::create([
                'user_id' => $request->user()->id,
                'event_id' => $event->id,
                'seats' => $requestedSeats,
                'ticket_type' => $request->ticket_type,
                'price_paid' => $price * $requestedSeats,
                'status' => 'pending', // Pending payment
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'country_code' => $request->country_code ?? null,
                'payment_meta' => json_encode(['method' => 'manual']), // Placeholder
            ]);

            $event->increment('tickets_sold', $requestedSeats);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Booking successful',
                'data' => $booking
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Booking failed', 'error' => $e->getMessage()], 500);
        }
    }
}
