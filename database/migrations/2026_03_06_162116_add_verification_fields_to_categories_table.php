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
            if (!Schema::hasColumn('categories', 'verification_requirements_en')) {
                $table->text('verification_requirements_en')->nullable()->after('requires_verification');
            }
            if (!Schema::hasColumn('categories', 'verification_requirements_ar')) {
                $table->text('verification_requirements_ar')->nullable()->after('verification_requirements_en');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['requires_verification', 'verification_requirements_en', 'verification_requirements_ar']);
        });
    }
};
