<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Transaction;
use App\Services\ThawaniService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            'service_request_id' => 'required|exists:service_requests,id',
        ]);

        $serviceRequest = ServiceRequest::findOrFail($request->service_request_id);

        // Prevent paying again
        if ($serviceRequest->payment_status === 'paid') {
            return response()->json(['status' => false, 'message' => 'Already paid'], 400);
        }

        $clientReference = 'TXN_' . uniqid();
        $amountInBaisa = (int) ($serviceRequest->price * 1000); // Thawani uses Baisa (1 OMR = 1000 Baisa)

        $data = [
            'client_reference_id' => $clientReference,
            'mode' => 'payment',
            'products' => [
                [
                    'name' => 'Service Request #' . $serviceRequest->id,
                    'quantity' => 1,
                    'unit_amount' => $amountInBaisa,
                ]
            ],
            'success_url' => route('payment.success'), // Web route to show success message in WebView
            'cancel_url' => route('payment.cancel'),   // Web route to show cancel message in WebView
            'metadata' => [
                'service_request_id' => $serviceRequest->id,
                'user_id' => $request->user()->id,
            ],
        ];

        try {
            $session = $this->thawaniService->createCheckoutSession($data);

            if (isset($session['data']['session_id'])) {
                // Create Pending Transaction
                Transaction::create([
                    'user_id' => $request->user()->id,
                    'service_request_id' => $serviceRequest->id,
                    'amount' => $serviceRequest->price,
                    'commission_amount' => 0, // Calculated after success
                    'provider_amount' => 0,   // Calculated after success
                    'status' => 'pending',
                    'payment_method' => 'thawani',
                    'transaction_reference' => $session['data']['session_id'], // Store Session ID here
                ]);

                // Construct Redirect URL
                // Sandbox URL: https://uatcheckout.thawani.al/pay/{session_id}?key={publishable_key}
                $publishableKey = config('services.thawani.publishable_key', 'HGvTMLDssJghr9tlQS6AgHe0GN5X9n');
                $redirectUrl = "https://uatcheckout.thawani.al/pay/{$session['data']['session_id']}?key={$publishableKey}";

                return response()->json([
                    'status' => true,
                    'message' => 'Payment session created',
                    'data' => [
                        'session_id' => $session['data']['session_id'],
                        'redirect_url' => $redirectUrl,
                    ]
                ]);
            }

            return response()->json(['status' => false, 'message' => 'Failed to create payment session', 'error' => $session], 500);

        } catch (\Exception $e) {
            Log::error('Thawani Payment Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Payment service error'], 500);
        }
    }

    public function webhook(Request $request)
    {
        $payload = $request->all(); // Thawani payload
        // Thawani sends status updates. We verify by calling GET session
        // Assuming payload contains session_id or we get it
        // Note: For simplicity, we might just assume success if we check status.
        // A better approach for WebView might be checking status manually on 'success_url' callback or polling.
        // But let's support webhook if configured.

        Log::info('Thawani Webhook:', $payload);

        return response()->json(['status' => true]);
    }

    // Callback method to call from the success page if needed, or simple status check
    public function checkStatus(Request $request)
    {
        $request->validate(['session_id' => 'required']);
        $response = $this->thawaniService->getPaymentStatus($request->session_id);

        if (isset($response['data']['payment_status']) && $response['data']['payment_status'] == 'paid') {
            // Fulfill order logic (similar to ServiceRequestController::pay logic)
            // Find transaction by session_id
            $txn = Transaction::where('transaction_reference', $request->session_id)->first();
            if ($txn && $txn->status == 'pending') {
                $txn->status = 'completed';
                $txn->save();

                // Update Service Request
                $serviceRequest = ServiceRequest::find($txn->service_request_id);
                if ($serviceRequest) {
                    $serviceRequest->payment_status = 'paid';
                    $serviceRequest->payment_transaction_id = $request->session_id;
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
            return response()->json(['status' => true, 'message' => 'Payment verified']);
        }

        return response()->json(['status' => false, 'message' => 'Payment not paid']);
    }
}
