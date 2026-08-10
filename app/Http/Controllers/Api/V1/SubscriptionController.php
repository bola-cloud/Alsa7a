<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Services\SubscriptionService;
use App\Services\ThawaniService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    protected $subscriptionService;
    protected $thawaniService;

    public function __construct(SubscriptionService $subscriptionService, ThawaniService $thawaniService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->thawaniService = $thawaniService;
    }

    /**
     * Get available subscription plans
     */
    public function plans(Request $request)
    {
        $countryId = $request->header('Country-Id');
        if (!$countryId && $user = $request->user('sanctum')) {
            $countryId = $user->country_id;
        }

        return response()->json([
            'status' => true,
            'data' => $this->subscriptionService->getPlans($countryId)
        ]);
    }

    /**
     * Start subscription checkout
     */
    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:monthly,annual',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $type = $request->type;
        
        $countryId = $request->header('Country-Id') ?: $user->country_id;
        $plans = $this->subscriptionService->getPlans($countryId);
        
        $plan = collect($plans)->firstWhere('id', $type);
        $price = $plan['price'];

        $clientReference = 'SUB' . uniqid();
        $amountInBaisa = (int) ($price * 1000);

        // Prepare Thawani Data
        $data = [
            'client_reference_id' => $clientReference,
            'mode' => 'payment',
            'products' => [
                [
                    'name' => "Alsa7a {$plan['name']}",
                    'quantity' => 1,
                    'unit_amount' => $amountInBaisa,
                ]
            ],
            'success_url' => route('payment.success', ['ref' => $clientReference]),
            'cancel_url' => route('payment.cancel'),
            'metadata' => [
                'user_id' => $user->id,
                'subscription_type' => $type,
                'type' => 'subscription'
            ],
        ];

        try {
            $session = $this->thawaniService->createCheckoutSession($data);

            if (isset($session['data']['session_id'])) {
                // Create Pending Subscription
                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'price' => $price,
                    'status' => 'pending'
                ]);

                // Create Transaction
                Transaction::create([
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'amount' => $price,
                    'status' => 'pending',
                    'payment_method' => 'thawani',
                    'transaction_reference' => $session['data']['session_id'],
                    'gateway_response' => ['client_reference_id' => $clientReference, 'type' => 'subscription']
                ]);

                $publishableKey = config('services.thawani.publishable_key', 'HGvTMLDssJghr9tlQS6AgHe0GN5X9n');
                $payUrl = config('services.thawani.pay_url', 'https://uatcheckout.thawani.om/pay');
                $payUrl = rtrim($payUrl, '/') . '/';
                $redirectUrl = "{$payUrl}{$session['data']['session_id']}?key={$publishableKey}";

                return response()->json([
                    'status' => true,
                    'message' => 'Subscription session created',
                    'data' => [
                        'session_id' => $session['data']['session_id'],
                        'redirect_url' => $redirectUrl,
                    ]
                ]);
            }

            return response()->json(['status' => false, 'message' => 'Failed to create payment session', 'error' => $session], 500);

        } catch (\Exception $e) {
            Log::error('Subscription Thawani Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Payment service error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get current user subscription status
     */
    public function status(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscription;

        return response()->json([
            'status' => true,
            'data' => [
                'is_subscribed' => $user->isSubscribed(),
                'subscription' => $subscription
            ]
        ]);
    }
}
