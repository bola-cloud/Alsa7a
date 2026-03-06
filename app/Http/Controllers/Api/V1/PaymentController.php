<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Transaction;
use App\Models\Subscription;
use App\Services\ThawaniService;
use App\Services\SubscriptionService;
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
            $price = $payable->price_paid;
            $name = 'Event Booking ' . $payable->id;

            if ($payable->status === 'confirmed') {
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
            // Append client ref so we can track it on return
            'success_url' => route('payment.success', ['ref' => $clientReference]),
            'cancel_url' => route('payment.cancel'),
            'metadata' => array_merge($meta, ['user_id' => $request->user()->id]),
        ];

        try {
            $session = $this->thawaniService->createCheckoutSession($data);

            if (isset($session['data']['session_id'])) {
                Transaction::create([
                    'user_id' => $request->user()->id,
                    'service_request_id' => $payableType == 'service_request' ? $payable->id : null,
                    'booking_id' => $payableType == 'booking' ? $payable->id : null,
                    'amount' => $price,
                    'status' => 'pending',
                    'payment_method' => 'thawani',
                    'transaction_reference' => $session['data']['session_id'],
                    // Store client ref so we can look it up if session_id is missing
                    'gateway_response' => ['client_reference_id' => $clientReference]
                ]);

                $publishableKey = config('services.thawani.publishable_key', 'HGvTMLDssJghr9tlQS6AgHe0GN5X9n');
                $payUrl = config('services.thawani.pay_url', 'https://uatcheckout.thawani.om/pay');
                $payUrl = rtrim($payUrl, '/') . '/';
                $redirectUrl = "{$payUrl}{$session['data']['session_id']}?key={$publishableKey}";

                \Illuminate\Support\Facades\Log::info("Payment Session Created: Session {$session['data']['session_id']} for Transation {$session['data']['session_id']}");

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
     * Check Payment Status
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
     * Handle Thawani Webhook (Robust Version)
     */
    public function webhook(Request $request)
    {
        Log::info('=== THAWANI WEBHOOK RECEIVED ===', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'ip' => $request->ip(),
        ]);

        try {
            $payload = $request->all();

            // Extract event data (handle various payload structures)
            $eventType = $payload['event_type'] ?? null;
            $sessionId = $payload['session_id'] ?? $payload['data']['session_id'] ?? null;
            $clientReferenceId = $payload['client_reference_id'] ?? $payload['data']['client_reference_id'] ?? null;
            $paymentStatus = $payload['payment_status'] ?? $payload['data']['payment_status'] ?? null;

            if (!$sessionId && !$clientReferenceId) {
                Log::error('Webhook missing session_id and client_reference_id');
                return response()->json(['status' => false, 'message' => 'Missing identifiers'], 400);
            }

            // Verify payment status with Thawani API
            $verifiedPaymentData = $this->verifyPaymentWithThawani($sessionId);

            if (!$verifiedPaymentData) {
                Log::error('Failed to verify payment with Thawani API', ['session_id' => $sessionId]);
                return response()->json(['status' => false, 'message' => 'Verification failed'], 400);
            }

            // Use verified data
            $paymentStatus = $verifiedPaymentData['payment_status'] ?? $paymentStatus;
            $finalSessionId = $verifiedPaymentData['session_id'] ?? $sessionId;

            // Find transaction
            $txn = Transaction::where('transaction_reference', $finalSessionId)->first();

            if (!$txn) {
                Log::error('Transaction not found for webhook', ['session_id' => $finalSessionId]);
                return response()->json(['status' => false, 'message' => 'Transaction not found'], 404);
            }

            Log::info('Transaction found for webhook', [
                'txn_id' => $txn->id,
                'status' => $txn->status,
                'payment_status' => $paymentStatus
            ]);

            // Handle Status
            if ($paymentStatus === 'paid') {
                if ($txn->status === 'completed' || $txn->status === 'paid') {
                    Log::info('Transaction already paid (idempotency)', ['txn_id' => $txn->id]);
                    return response()->json(['status' => true, 'message' => 'Already processed']);
                }

                $this->processPaymentUpdate($txn, 'paid');
                return response()->json(['status' => true, 'message' => 'Processed successfully']);

            } elseif (in_array($paymentStatus, ['cancelled', 'failed', 'unpaid'])) {
                $this->processPaymentUpdate($txn, $paymentStatus);
                return response()->json(['status' => true, 'message' => 'Processed status update']);
            } else {
                Log::warning('Unhandled payment status', ['status' => $paymentStatus]);
                return response()->json(['status' => true, 'message' => 'Status ignored']);
            }

        } catch (\Exception $e) {
            Log::error('Webhook processing exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['status' => false, 'message' => 'Internal error'], 500);
        }
    }

    /**
     * Verify payment directly with Thawani API
     */
    protected function verifyPaymentWithThawani($sessionId)
    {
        if (!$sessionId)
            return null;

        try {
            // Using the service directly which handles the URL and keys
            $paymentData = $this->thawaniService->getPaymentStatus($sessionId);

            if (isset($paymentData['data']['payment_status'])) {
                Log::info('Thawani payment verification successful', [
                    'session_id' => $sessionId,
                    'status' => $paymentData['data']['payment_status']
                ]);
                return $paymentData['data'];
            }

            Log::warning('Thawani verification returned invalid structure', ['response' => $paymentData]);
            return null;

        } catch (\Exception $e) {
            Log::error('Failed to verify payment with Thawani API', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Success Callback (User Redirect)
     */
    public function success(Request $request)
    {
        Log::info('=== PAYMENT CALLBACK SUCCESS ===', [
            'all_request' => $request->all(),
            'query_string' => $request->getQueryString(),
        ]);

        $sessionId = $request->query('payment_id')
            ?? $request->query('session_id')
            ?? $request->query('transaction_id');

        $clientRef = $request->query('ref') ?? $request->query('client_reference_id');

        $txn = null;

        // Strategy 1: Find by Session ID
        if ($sessionId) {
            $txn = Transaction::where('transaction_reference', $sessionId)->first();
        }

        // Strategy 2: Find by Client Ref (if Session ID missing)
        if (!$txn && $clientRef) {
            // Search in JSON column
            $txn = Transaction::where('gateway_response->client_reference_id', $clientRef)->first();
            if ($txn) {
                $sessionId = $txn->transaction_reference; // Recover Session ID from DB
                Log::info('Recovered Session ID from Client Ref', ['session_id' => $sessionId, 'ref' => $clientRef]);
            }
        }

        if (!$txn) {
            Log::error('Transaction not found in callback', ['session_id' => $sessionId, 'ref' => $clientRef]);
            return response('<html><body><h1 style="color:red;text-align:center;">Transaction Not Found!</h1></body></html>', 404);
        }

        Log::info('Transaction found in callback', [
            'txn_id' => $txn->id,
            'status' => $txn->status
        ]);

        if ($txn->status === 'completed' || $txn->status === 'paid') {
            Log::info('Transaction already paid (webhook processed first)');
            return response('<html><body><h1 style="color:green;text-align:center;margin-top:50px;">Payment Successful</h1><p style="text-align:center;">Your payment has been confirmed. You can return to the application.</p></body></html>');
        }

        // Webhook hasn't processed it yet.
        // Attempt immediate verification using recovered Session ID
        if ($sessionId) {
            Log::info('Payment pending webhook. Attempting immediate verification...');
            $verifiedData = $this->verifyPaymentWithThawani($sessionId);

            if ($verifiedData && isset($verifiedData['payment_status']) && $verifiedData['payment_status'] === 'paid') {
                $this->processPaymentUpdate($txn, 'paid');
                return response('<html><body><h1 style="color:green;text-align:center;margin-top:50px;">Payment Successful</h1><p style="text-align:center;">Transaction verified and completed. You can return to the application.</p></body></html>');
            }
        }

        // If generic verification failed or still unpaid, show processing
        return response('<html><body><h1 style="color:orange;text-align:center;margin-top:50px;">Processing Payment...</h1><p style="text-align:center;">Your payment is being processed. Please wait comfortably or check your status in the app shortly.</p></body></html>');
    }

    /**
     * Cancel Callback
     */
    /**
     * Cancel Callback
     */
    public function cancel(Request $request)
    {
        Log::info('=== PAYMENT CALLBACK CANCEL ===', $request->all());

        $sessionId = $request->query('payment_id')
            ?? $request->query('session_id')
            ?? $request->query('transaction_id');

        $clientRef = $request->query('ref') ?? $request->query('client_reference_id');

        $txn = null;

        if ($sessionId) {
            $txn = Transaction::where('transaction_reference', $sessionId)->first();
        }

        if (!$txn && $clientRef) {
            $txn = Transaction::where('gateway_response->client_reference_id', $clientRef)->first();
        }

        if ($txn) {
            // Explicitly mark as failed/cancelled
            $this->processPaymentUpdate($txn, 'cancelled');
            Log::info('Transaction cancelled via callback', ['txn_id' => $txn->id]);
        } else {
            Log::warning('Transaction not found in cancel callback', ['session_id' => $sessionId, 'ref' => $clientRef]);
        }

        return response('<html><body><h1 style="color:red;text-align:center;margin-top:50px;">Payment Cancelled</h1><p style="text-align:center;">You have cancelled the payment.</p></body></html>');
    }

    /**
     * Process Payment Update Helper
     */
    protected function processPaymentUpdate($txn, $status)
    {
        Log::info("Processing Payment Update: Txn {$txn->id} -> $status");

        if ($status === 'paid' && $txn->status !== 'completed') {

            DB::beginTransaction();
            try {
                // 1. Update Transaction
                $txn->update([
                    'status' => 'paid', // Or completed
                    'gateway_response' => ['status' => $status, 'updated_at' => now()]
                ]);

                // 2. Update Related ServiceRequest
                if ($txn->service_request_id) {
                    $req = ServiceRequest::find($txn->service_request_id);
                    if ($req) {
                        $req->update([
                            'payment_status' => 'paid',
                            // 'status' => 'paid', // Removed: Service status shouldn't change to 'paid', it stays 'accepted' (or whatever it was)
                            'payment_transaction_id' => $txn->id, // Link to local transaction
                            'payment_meta' => $txn->gateway_response // Propagate gateway details
                        ]);
                        Log::info("Service Request {$req->id} marked as paid.");
                    }
                }

                // 3. Update Related Booking
                if ($txn->booking_id) {
                    $booking = \App\Models\Booking::find($txn->booking_id);
                    if ($booking) {
                        $booking->update([
                            'status' => 'confirmed',
                            'payment_status' => 'paid',
                            'payment_meta' => json_encode(['method' => 'thawani', 'paid_at' => now()])
                        ]);
                        Log::info("Booking {$booking->id} confirmed.");
                    }
                }

                // 4. Update Related Subscription
                if ($txn->subscription_id) {
                    $sub = Subscription::find($txn->subscription_id);
                    if ($sub) {
                        app(SubscriptionService::class)->activateSubscription($sub);
                        Log::info("Subscription {$sub->id} activated.");
                    }
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Failed to commit payment update for Txn {$txn->id}: " . $e->getMessage());
            }

        } elseif ($status === 'cancelled' || $status === 'failed') {
            $txn->update(['status' => 'failed']);
            Log::info("Txn {$txn->id} marked as failed.");
        }
    }
}
