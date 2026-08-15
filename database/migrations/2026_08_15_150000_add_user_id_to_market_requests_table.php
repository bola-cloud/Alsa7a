<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The table originally assumed only a club owner could post a job
     * (club_id was required). The real rule is category-based: any user
     * whose category has `is_marketplace` enabled can post, club or no club.
     * The table is empty in production, so this is a safe, zero-downtime
     * schema fix rather than a backfill.
     */
    public function up(): void
    {
        Schema::table('market_requests', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')
                ->constrained('users')->cascadeOnDelete();
        });

        // doctrine/dbal (needed for Blueprint::change()) is not installed on
        // this project, so club_id's NOT NULL + FK are dropped and re-added
        // via raw SQL instead of a column-modify.
        Schema::table('market_requests', function (Blueprint $table) {
            $table->dropForeign(['club_id']);
        });

        DB::statement('ALTER TABLE market_requests MODIFY club_id BIGINT UNSIGNED NULL');

        Schema::table('market_requests', function (Blueprint $table) {
            $table->foreign('club_id')->references('id')->on('clubs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            $table->dropForeign(['club_id']);
        });

        DB::statement('ALTER TABLE market_requests MODIFY club_id BIGINT UNSIGNED NOT NULL');

        Schema::table('market_requests', function (Blueprint $table) {
            $table->foreign('club_id')->references('id')->on('clubs')->cascadeOnDelete();
        });
    }
};
