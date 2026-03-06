<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Traits\FormatsProfileData;

class EventBookingController extends Controller
{
    use FormatsProfileData;
    /**
     * Book Event Ticket (Protected).
     */
    public function store(Request $request, $id)
    {
        $event = Event::with(['club.owner.subscription', 'club.owner.category'])->find($id);

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
                'ticket_number' => 'TKT-' . strtoupper(uniqid()),
                'status' => ($price * $requestedSeats) == 0 ? 'confirmed' : 'pending', // Auto-confirm if free
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'country_code' => $request->country_code ?? null,
                'payment_meta' => json_encode(['method' => ($price * $requestedSeats) == 0 ? 'free' : 'manual']),
            ]);

            $event->increment('tickets_sold', $requestedSeats);

            DB::commit();

            $booking->load(['event.club.owner.subscription', 'event.club.owner.category', 'event.sport']);

            $currentUser = $request->user();
            if ($booking->event && $booking->event->club && $booking->event->club->owner) {
                $booking->event->club_profile = $this->getProfileData($booking->event->club->owner, false, $currentUser);
            }
            // Add requester profile as well
            $booking->user_profile = $this->getProfileData($currentUser, false, $currentUser);

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
    /**
     * List my Event Bookings.
     * GET /my-bookings
     */
    public function index(Request $request)
    {
        $bookings = Booking::where('user_id', $request->user()->id)
            ->with(['event.club.owner.subscription', 'event.club.owner.category', 'event.sport', 'user.subscription', 'user.category', 'user.club'])
            ->latest()
            ->paginate(10);

        $currentUser = $request->user();
        // Transform to include explicit payment status if needed
        $bookings->getCollection()->transform(function ($booking) use ($currentUser) {
            $booking->payment_status = $booking->status === 'confirmed' ? 'paid' : 'pending';
            if ($booking->event && $booking->event->club && $booking->event->club->owner) {
                $booking->event->club_profile = $this->getProfileData($booking->event->club->owner, false, $currentUser);
            }
            if ($booking->user) {
                $booking->user_profile = $this->getProfileData($booking->user, false, $currentUser);
            }
            return $booking;
        });

        return response()->json([
            'status' => true,
            'data' => $bookings,
            'message' => 'Bookings retrieved successfully'
        ]);
    }
}
