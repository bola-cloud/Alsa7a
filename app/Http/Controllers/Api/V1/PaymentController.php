<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Transaction;
use App\Services\ThawaniService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $thawaniService;

    public function __construct(ThawaniService $thawaniService)
    {
        $this->thawaniService = $thawaniService;
    }

    public function pay(Request $request)
    {
        $request->validate([
            'service_request_id' => 'nullable|exists:service_requests,id',
            'booking_id' => 'nullable|exists:bookings,id',
        ]);

        if (!$request->service_request_id && !$request->booking_id) {
            return response()->json(['status' => false, 'message' => 'Service Request ID or Booking ID is required'], 400);
        }

        $payable = null;
        $payableType = null;
        $price = 0;
        $name = '';
        $meta = [];

        if ($request->service_request_id) {
            $payable = ServiceRequest::findOrFail($request->service_request_id);
            $payableType = 'service_request';
            $price = $payable->price;
            $name = 'Service Request #' . $payable->id;

            if ($payable->payment_status === 'paid') {
                return response()->json(['status' => false, 'message' => 'Already paid'], 400);
            }
            if ($payable->status !== 'accepted') {
                return response()->json(['status' => false, 'message' => 'Service request must be accepted before payment'], 400);
            }
            $meta['service_request_id'] = $payable->id;
        } elseif ($request->booking_id) {
            $payable = \App\Models\Booking::findOrFail($request->booking_id);
            $payableType = 'booking';
            $price = $payable->price_paid; // Logic handles full price calculation
            $name = 'Event Booking #' . $payable->id;

            if ($payable->status === 'confirmed') { // Assuming confirmed implies paid for paid events
                return response()->json(['status' => false, 'message' => 'Already paid/confirmed'], 400);
            }
            $meta['booking_id'] = $payable->id;
        }

        $clientReference = 'TXN_' . uniqid();
        $amountInBaisa = (int) ($price * 1000);

        $data = [
            'client_reference_id' => $clientReference,
            'mode' => 'payment',
            'products' => [
                [
                    'name' => $name,
                    'quantity' => 1,
                    'unit_amount' => $amountInBaisa,
                ]
            ],
            'success_url' => route('payment.success'),
            'cancel_url' => route('payment.cancel'),
            'metadata' => array_merge($meta, ['user_id' => $request->user()->id]),
        ];

        try {
            $session = $this->thawaniService->createCheckoutSession($data);

            if (isset($session['data']['session_id'])) {
                // Create Pending Transaction
                Transaction::create([
                    'user_id' => $request->user()->id,
                    'service_request_id' => $payableType == 'service_request' ? $payable->id : null,
                    'booking_id' => $payableType == 'booking' ? $payable->id : null,
                    'amount' => $price,
                    'status' => 'pending',
                    'payment_method' => 'thawani',
                    'transaction_reference' => $session['data']['session_id'],
                ]);

                $publishableKey = config('services.thawani.publishable_key', 'HGvTMLDssJghr9tlQS6AgHe0GN5X9n');
                $redirectUrl = "https://uatcheckout.thawani.om/pay/{$session['data']['session_id']}?key={$publishableKey}";

                return response()->json([
                    'status' => true,
                    'message' => 'Payment session created',
                    'data' => [
                        'session_id' => $session['data']['session_id'],
                        'redirect_url' => $redirectUrl,
                        'success_url' => route('payment.success'),
                        'cancel_url' => route('payment.cancel'),
                    ]
                ]);
            }

            return response()->json(['status' => false, 'message' => 'Failed to create payment session', 'error' => $session], 500);

        } catch (\Exception $e) {
            Log::error('Thawani Payment Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Payment service error: ' . $e->getMessage()], 500);
        }
    }

    public function webhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Thawani Webhook:', $payload);

        // Thawani payload usually has 'session_id' or 'data.session_id' depending on event
        // We will try to extract session_id and force a check
        $sessionId = $payload['data']['session_id'] ?? ($payload['session_id'] ?? null);

        if ($sessionId) {
            $this->updatePaymentStatus($sessionId);
        }

        return response()->json(['status' => true]);
    }

    // Callback method to call from the success page if needed, or simple status check
    public function checkStatus(Request $request)
    {
        $request->validate(['session_id' => 'required']);
        $result = $this->updatePaymentStatus($request->session_id);

        if ($result) {
            return response()->json(['status' => true, 'message' => 'Payment verified']);
        }

        return response()->json(['status' => false, 'message' => 'Payment not paid or failed']);
    }

    /**
     * Centralized method to verify and update payment status
     */
    protected function updatePaymentStatus($sessionId)
    {
        // 1. Check Thawani API
        $response = $this->thawaniService->getPaymentStatus($sessionId);

        if (isset($response['data']['payment_status']) && $response['data']['payment_status'] == 'paid') {

            $txn = Transaction::where('transaction_reference', $sessionId)->first();

            // Allow idempotency: if already completed, return true
            if ($txn && $txn->status == 'completed') {
                return true;
            }

            if ($txn && $txn->status == 'pending') {
                DB::transaction(function () use ($txn, $response, $sessionId) {
                    $txn->status = 'completed';
                    $txn->gateway_response = json_encode($response['data']);
                    $txn->save();

                    // Handle Service Request
                    if ($txn->service_request_id) {
                        $serviceRequest = ServiceRequest::find($txn->service_request_id);
                        if ($serviceRequest) {
                            $serviceRequest->payment_status = 'paid';
                            $serviceRequest->payment_transaction_id = $sessionId;
                            $serviceRequest->save();

                            // Commission Calculation
                            $commissionRate = setting('service_commission', 10);
                            $txn->commission_amount = $txn->amount * ($commissionRate / 100);
                            $txn->provider_amount = $txn->amount - $txn->commission_amount;
                            $txn->save();

                            // Create Conversation
                            \App\Models\Conversation::firstOrCreate([
                                'service_request_id' => $serviceRequest->id
                            ], [
                                'user_one_id' => $serviceRequest->requester_id,
                                'user_two_id' => $serviceRequest->provider_id
                            ]);
                        }
                    }
                    // Handle Event Booking
                    elseif ($txn->booking_id) {
                        $booking = \App\Models\Booking::find($txn->booking_id);
                        if ($booking) {
                            $booking->status = 'confirmed';

                            // Payment meta update
                            $meta = is_string($booking->payment_meta) ? json_decode($booking->payment_meta, true) : ($booking->payment_meta ?? []);
                            if (!is_array($meta))
                                $meta = [];
                            $meta['transaction_id'] = $sessionId;
                            $meta['method'] = 'thawani';
                            $booking->payment_meta = $meta;
                            $booking->save();
                        }
                    }
                });

                return true;
            }
        }

        return false;
    }
}
