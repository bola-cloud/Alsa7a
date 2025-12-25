<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Drop single columns
            if (Schema::hasColumn('events', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('events', 'description')) {
                $table->dropColumn('description');
            }

            // Add localized columns
            if (!Schema::hasColumn('events', 'title_en')) {
                $table->string('title_en')->after('sport_id');
            }
            if (!Schema::hasColumn('events', 'title_ar')) {
                $table->string('title_ar')->after('title_en')->nullable();
            }
            if (!Schema::hasColumn('events', 'description_en')) {
                $table->text('description_en')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('events', 'description_ar')) {
                $table->text('description_ar')->nullable()->after('description_en');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'title_ar', 'description_en', 'description_ar']);
            $table->string('title');
            $table->text('description')->nullable();
        });
    }
};
