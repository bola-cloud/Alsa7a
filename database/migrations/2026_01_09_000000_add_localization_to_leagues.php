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
        Schema::table('leagues', function (Blueprint $table) {
            if (!Schema::hasColumn('leagues', 'name_en')) {
                $table->string('name_en')->nullable()->after('name');
            }
            if (!Schema::hasColumn('leagues', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name_en');
            }
            if (!Schema::hasColumn('leagues', 'description_en')) {
                $table->text('description_en')->nullable()->after('description');
            }
            if (!Schema::hasColumn('leagues', 'description_ar')) {
                $table->text('description_ar')->nullable()->after('description_en');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ar', 'description_en', 'description_ar']);
        });
    }
};
