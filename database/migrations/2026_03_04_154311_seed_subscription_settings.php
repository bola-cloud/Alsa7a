<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')->insert([
            [
                'key' => 'subscription_monthly_price',
                'value' => '5.000',
                'type' => 'text',
                'group' => 'subscription',
                'label' => 'Monthly Subscription Price (OMR)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'subscription_annual_price',
                'value' => '50.000',
                'type' => 'text',
                'group' => 'subscription',
                'label' => 'Annual Subscription Price (OMR)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['subscription_monthly_price', 'subscription_annual_price'])->delete();
    }
};
