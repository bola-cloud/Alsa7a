<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('club_id')->nullable();
            $table->unsignedBigInteger('sport_id')->nullable();
            $table->string('title');
            $table->string('slug')->nullable()->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('currency', 8)->default('USD');
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('set null');
            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('set null');
            $table->index(['provider_id', 'club_id', 'sport_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
