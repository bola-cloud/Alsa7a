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
        $tables = ['users', 'posts', 'stories', 'services', 'clubs', 'events'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (!Schema::hasColumn($table, 'country_id')) {
                    $blueprint->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['users', 'posts', 'stories', 'services', 'clubs', 'events'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (Schema::hasColumn($table, 'country_id')) {
                    $blueprint->dropForeign(['country_id']);
                    $blueprint->dropColumn('country_id');
                }
            });
        }
    }
};
