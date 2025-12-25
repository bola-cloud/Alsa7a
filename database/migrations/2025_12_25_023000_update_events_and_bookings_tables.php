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
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'ticket_types')) {
                $table->json('ticket_types')->nullable()->after('price');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'ticket_type')) {
                $table->string('ticket_type')->nullable()->after('seats');
            }
            if (!Schema::hasColumn('bookings', 'name')) {
                $table->string('name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('bookings', 'email')) {
                $table->string('email')->nullable()->after('name');
            }
            if (!Schema::hasColumn('bookings', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('bookings', 'country_code')) {
                $table->string('country_code')->nullable()->after('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('ticket_types');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['ticket_type', 'name', 'email', 'phone', 'country_code']);
        });
    }
};
