<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('provider_id');
            $table->string('status')->default('pending'); // pending, accepted, rejected, completed, canceled
            $table->text('message')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->json('payment_meta')->nullable();
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('requester_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['service_id', 'requester_id', 'provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
