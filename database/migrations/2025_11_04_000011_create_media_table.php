<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('mediaable_type');
            $table->unsignedBigInteger('mediaable_id');
            $table->string('url');
            $table->string('type')->default('image');
            $table->string('title')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index(['mediaable_type', 'mediaable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
