<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_sport', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->unsignedBigInteger('sport_id');
            $table->json('meta')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('cascade');
            $table->unique(['club_id', 'sport_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_sport');
    }
};
