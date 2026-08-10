<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->decimal('subscription_monthly_price', 8, 3)->default(5.000);
            $table->decimal('subscription_annual_price', 8, 3)->default(50.000);
            $table->string('currency', 10)->default('OMR');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['subscription_monthly_price', 'subscription_annual_price', 'currency']);
        });
    }
};
