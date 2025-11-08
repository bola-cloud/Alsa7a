<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('club_id')->nullable();
            $table->string('name');
            $table->string('slug')->nullable()->unique();
            $table->string('position')->nullable();
            $table->integer('number')->nullable();
            $table->string('nationality')->nullable();
            $table->string('profile_photo')->nullable();
            $table->json('stats')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('set null');
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('set null');
            $table->index(['team_id', 'club_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
