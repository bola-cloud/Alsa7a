<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('league_id');
            $table->string('title');
            $table->string('url');
            $table->integer('duration_seconds')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('league_id')->references('id')->on('leagues')->onDelete('cascade');
            $table->index('league_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_videos');
    }
};
