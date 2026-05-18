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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('hls_url')->nullable()->after('image');
            $table->enum('processing_status', ['completed', 'pending', 'processing', 'failed'])->default('completed')->after('hls_url');
        });

        Schema::table('community_posts', function (Blueprint $table) {
            $table->string('hls_url')->nullable()->after('image');
            $table->enum('processing_status', ['completed', 'pending', 'processing', 'failed'])->default('completed')->after('hls_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['hls_url', 'processing_status']);
        });

        Schema::table('community_posts', function (Blueprint $table) {
            $table->dropColumn(['hls_url', 'processing_status']);
        });
    }
};
