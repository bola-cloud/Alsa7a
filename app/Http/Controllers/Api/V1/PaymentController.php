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
            $name = 'Service Request ' . $payable->id;

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
            $name = 'Event Booking ' . $payable->id;

            if ($payable->status === 'confirmed') { // Assuming confirmed implies paid for paid events
                return response()->json(['status' => false, 'message' => 'Already paid/confirmed'], 400);
            }
            $meta['booking_id'] = $payable->id;
        }

        $clientReference = 'TXN' . uniqid();
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
                $payUrl = config('services.thawani.pay_url', 'https://uatcheckout.thawani.om/pay');
                // Ensure Pay URL ends with slash or handle clean concatenation
                $payUrl = rtrim($payUrl, '/') . '/';
                $redirectUrl = "{$payUrl}{$session['data']['session_id']}?key={$publishableKey}";

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

    /**
     * Check Payment Status (Manual Poll or user return).
     */
    public function checkStatus(Request $request)
    {
        $sessionId = $request->session_id;
        
        if (!$sessionId) {
             return response()->json(['status' => false, 'message' => 'Session ID required'], 400);
        }

        $paymentData = $this->thawaniService->getPaymentStatus($sessionId);
        
        if (isset($paymentData['data']['payment_status'])) {
             $status = $paymentData['data']['payment_status'];
             
             // Update Transaction if found
             $txn = Transaction::where('transaction_reference', $sessionId)->first();
             if ($txn) {
                 $this->processPaymentUpdate($txn, $status);
             }

             return response()->json([
                 'status' => true,
                 'payment_status' => $status,
                 'data' => $paymentData['data']
             ]);
        }

        return response()->json(['status' => false, 'message' => 'Could not fetch status'], 500);
    }

    /**
     * Webhook Handler from Thawani.
     */
    public function webhook(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('--- Thawani Webhook Hit ---', $request->all());

        $payload = $request->all();
        $sessionId = $payload['session_id'] ?? $payload['data']['session_id'] ?? null; // Handle nested data if Thawani sends it that way
        $eventType = $payload['event_type'] ?? null;
        
        if (!$sessionId) {
            \Illuminate\Support\Facades\Log::error('Webhook: No session ID found in payload');
            return response()->json(['status' => false, 'message' => 'No session ID'], 400);
        }

        // Verify with Thawani API directly for security
        try {
            $paymentData = $this->thawaniService->getPaymentStatus($sessionId);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Webhook: Thawani API Check Failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Verification check failed'], 500);
        }
        
        if (!isset($paymentData['data']['payment_status'])) {
             \Illuminate\Support\Facades\Log::error('Webhook: Verification Data Invalid', ['session_id' => $sessionId, 'response' => $paymentData]);
             return response()->json(['status' => false, 'message' => 'Verification failed'], 400);
        }

        $status = $paymentData['data']['payment_status'];
        \Illuminate\Support\Facades\Log::info("Webhook: Verified Status for $sessionId is $status");
        
        // Find Transaction
        $txn = Transaction::where('transaction_reference', $sessionId)->first();
        
        if (!$txn) {
             \Illuminate\Support\Facades\Log::error('Webhook: Transaction Not Found', ['session_id' => $sessionId]);
             return response()->json(['status' => false, 'message' => 'Transaction not found'], 404);
        }

        // Idempotency check
        if ($txn->status === 'completed' || $txn->status === 'paid') {
             \Illuminate\Support\Facades\Log::info('Webhook: Already Processed', ['txn_id' => $txn->id]);
             return response()->json(['status' => true, 'message' => 'Already processed']);
        }

        $this->processPaymentUpdate($txn, $status);

        return response()->json(['status' => true, 'message' => 'Processed']);
    }

    /**
     * Internal helper to update Transaction and Related Models.
     */
    protected function processPaymentUpdate($txn, $status)
    {
        \Illuminate\Support\Facades\Log::info("Processing Payment Update: Txn {$txn->id} -> $status");

        if ($status === 'paid' && $txn->status !== 'completed') {
            
            // 1. Update Transaction
            $txn->update([
                'status' => 'completed',
                'gateway_response' => ['status' => $status, 'updated_at' => now()]
            ]);

            // 2. Update Related ServiceRequest
            if ($txn->service_request_id) {
                $req = ServiceRequest::find($txn->service_request_id);
                if ($req) {
                    $req->update(['payment_status' => 'paid', 'status' => 'paid']); 
                    \Illuminate\Support\Facades\Log::info("Service Request {$req->id} marked as paid.");
                }
            }

            // 3. Update Related Booking
            if ($txn->booking_id) {
                $booking = \App\Models\Booking::find($txn->booking_id);
                if ($booking) {
                    $booking->update([
                        'status' => 'confirmed', // Paid = Confirmed
                        'payment_meta' => json_encode(['method' => 'thawani', 'paid_at' => now()])
                    ]);
                    \Illuminate\Support\Facades\Log::info("Booking {$booking->id} confirmed.");
                }
            }

        } elseif ($status === 'cancelled' || $status === 'failed') {
             $txn->update(['status' => 'failed']);
             \Illuminate\Support\Facades\Log::info("Txn {$txn->id} marked as failed.");
        }
    }

    // Success & Cancel methods for Redirects (called from web.php)
    public function success(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('--- Payment Success Page Hit ---', $request->all());

        $sessionId = $request->payment_id ?? $request->session_id;

        if ($sessionId) {
            // Instant verification in case webhook is slow
            try {
                $paymentData = $this->thawaniService->getPaymentStatus($sessionId);
                if (isset($paymentData['data']['payment_status'])) {
                    $txn = Transaction::where('transaction_reference', $sessionId)->first();
                    if ($txn) {
                        $this->processPaymentUpdate($txn, $paymentData['data']['payment_status']);
                    }
                }
            } catch (\Exception $e) {
                // Ignore error on success page
                \Illuminate\Support\Facades\Log::error('Success Page: Check Failed', ['error' => $e->getMessage()]);
            }
        }

        return response('<html><body><h1 style="color:green;text-align:center;margin-top:50px;">Payment Successful</h1><p style="text-align:center;">You can return to the application.</p></body></html>');
    }

    public function cancel(Request $request)
    {
         \Illuminate\Support\Facades\Log::info('--- Payment Cancel Page Hit ---', $request->all());
         return response('<html><body><h1 style="color:red;text-align:center;margin-top:50px;">Payment Cancelled</h1><p style="text-align:center;">You can return to the application.</p></body></html>');
    }
}
