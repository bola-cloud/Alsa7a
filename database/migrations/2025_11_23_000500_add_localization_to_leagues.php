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
        if (! Schema::hasColumn('leagues', 'name_en')) {
            Schema::table('leagues', function (Blueprint $table) {
                $table->string('name_en')->nullable()->after('name');
                $table->string('name_ar')->nullable()->after('name_en');
                $table->text('description_en')->nullable()->after('description');
                $table->text('description_ar')->nullable()->after('description_en');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('leagues', 'name_en')) {
            Schema::table('leagues', function (Blueprint $table) {
                $table->dropColumn(['name_en','name_ar','description_en','description_ar']);
            });
        }
    }
};
