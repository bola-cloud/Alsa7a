<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('phone_verification_code', 10)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->foreignId('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();

            // Profile / player / provider fields (nullable)
            $table->string('profile_title')->nullable();
            $table->text('bio')->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();

            // Optional links to team/club if user is a player/postulant
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('club_id')->nullable();

            // Player-like fields stored on users
            $table->string('position')->nullable();
            $table->integer('number')->nullable();
            $table->string('nationality')->nullable();
            $table->json('stats')->nullable();
            $table->boolean('is_featured')->default(false);

            // Availability or schedule for providers
            $table->json('availability')->nullable();

            $table->timestamps();

            // Foreign keys to teams and clubs
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('set null');
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
