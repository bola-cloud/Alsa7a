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
        // add image column and remove slug (drop unique index first)
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'image')) {
                $table->string('image')->nullable()->after('name');
            }
        });

        // drop unique slug index if exists, then drop slug column
        if (Schema::hasColumn('categories', 'slug')) {
            Schema::table('categories', function (Blueprint $table) {
                // attempt to drop index by name
                try {
                    $table->dropUnique('categories_slug_unique');
                } catch (\Exception $e) {
                    // ignore if index does not exist
                }

                $table->dropColumn('slug');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};
