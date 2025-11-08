<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->unsignedBigInteger('sport_id');
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('slug')->unique();
            $table->string('jersey_color')->nullable();
            $table->string('coach')->nullable();
            $table->year('founded_year')->nullable();
            $table->boolean('active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('restrict');
            $table->index(['club_id', 'sport_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
