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
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'parent_category_id')) {
                $table->unsignedBigInteger('parent_category_id')->nullable()->after('id');
                $table->foreign('parent_category_id')->references('id')->on('parent_categories')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_category_id']);
            $table->dropColumn('parent_category_id');
        });
    }
};
