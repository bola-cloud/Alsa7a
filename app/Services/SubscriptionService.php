<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * Get pricing for subscription plans
     */
    public function getPlans()
    {
        return [
            [
                'id' => 'monthly',
                'name' => 'Monthly Plan',
                'price' => (float) setting('subscription_monthly_price', 5.000),
                'currency' => 'OMR',
                'duration' => '1 Month'
            ],
            [
                'id' => 'annual',
                'name' => 'Annual Plan',
                'price' => (float) setting('subscription_annual_price', 50.000),
                'currency' => 'OMR',
                'duration' => '1 Year'
            ]
        ];
    }

    /**
     * Activate a subscription after successful payment
     */
    public function activateSubscription(Subscription $subscription)
    {
        DB::beginTransaction();
        try {
            $startDate = now();
            $endDate = $subscription->type === 'monthly'
                ? now()->addMonth()
                : now()->addYear();

            $subscription->update([
                'status' => 'active',
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            DB::commit();
            return $subscription;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
