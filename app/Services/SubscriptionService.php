<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Country;

class SubscriptionService
{
    /**
     * Get pricing for subscription plans
     *
     * @param int|null $countryId
     */
    public function getPlans($countryId = null)
    {
        $monthlyPrice = (float) setting('subscription_monthly_price', 5.000);
        $annualPrice = (float) setting('subscription_annual_price', 50.000);
        $currency = 'OMR';

        if ($countryId) {
            $country = Country::find($countryId);
            if ($country) {
                // If the country has values, use them. Otherwise fallback to global settings.
                $monthlyPrice = (float) ($country->subscription_monthly_price ?? $monthlyPrice);
                $annualPrice = (float) ($country->subscription_annual_price ?? $annualPrice);
                $currency = $country->currency ?? $currency;
            }
        }

        return [
            [
                'id' => 'monthly',
                'name' => 'Monthly Plan',
                'price' => $monthlyPrice,
                'currency' => strtoupper($currency),
                'duration' => '1 Month'
            ],
            [
                'id' => 'annual',
                'name' => 'Annual Plan',
                'price' => $annualPrice,
                'currency' => strtoupper($currency),
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
