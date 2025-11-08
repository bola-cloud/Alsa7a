<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id')->nullable();
            $table->unsignedBigInteger('sport_id')->nullable();
            $table->string('title');
            $table->string('slug')->nullable()->unique();
            $table->text('description')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->string('venue')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('capacity')->nullable();
            $table->integer('tickets_sold')->default(0);
            $table->string('featured_image')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('set null');
            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('set null');
            $table->index(['club_id', 'sport_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
