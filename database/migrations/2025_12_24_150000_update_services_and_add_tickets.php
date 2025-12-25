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
        // Update services table
        Schema::table('services', function (Blueprint $table) {
            $table->string('location')->nullable()->after('description'); // For venue/location
            $table->json('days_available')->nullable()->after('meta'); // ['Mon', 'Tue']
        });

        // Update service_requests table
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dateTime('end_at')->nullable()->after('scheduled_at');
            $table->string('payment_status')->default('pending')->after('price'); // pending, held, released, refunded
            $table->string('payment_transaction_id')->nullable()->after('payment_status');
            $table->boolean('is_disputed')->default(false)->after('status');
        });

        // Create tickets table for admin disputes/issues
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // User who opened the ticket
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('open'); // open, in_progress, resolved, closed
            $table->string('priority')->default('medium'); // low, medium, high
            // Polymorphic relation to connect ticket to a service request, order, etc.
            $table->nullableMorphs('ticketable');
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn(['end_at', 'payment_status', 'payment_transaction_id', 'is_disputed']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['location', 'days_available']);
        });
    }
};
