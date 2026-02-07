<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update services table: Change default to OMR and update existing rows
        Schema::table('services', function (Blueprint $table) {
            $table->string('currency', 8)->default('OMR')->change();
        });

        DB::table('services')->update(['currency' => 'OMR']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('currency', 8)->default('USD')->change();
        });
    }
};
