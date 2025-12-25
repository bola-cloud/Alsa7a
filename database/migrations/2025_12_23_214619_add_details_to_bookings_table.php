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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('ticket_number')->unique()->after('id');
            $table->string('ticket_type')->default('regular')->after('ticket_number'); // regular, vip
            $table->string('name')->nullable()->after('user_id'); // Guest name or specific ticket holder
            $table->string('email')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['ticket_number', 'ticket_type', 'name', 'email', 'phone']);
        });
    }
};
