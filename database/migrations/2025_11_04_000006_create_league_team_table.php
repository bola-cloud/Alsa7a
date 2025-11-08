<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_team', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('league_id');
            $table->unsignedBigInteger('team_id');
            $table->string('group')->nullable();
            $table->integer('seed')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('league_id')->references('id')->on('leagues')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->unique(['league_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_team');
    }
};
