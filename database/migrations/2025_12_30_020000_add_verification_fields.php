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
        // Add verification flag to categories
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'requires_verification')) {
                $table->boolean('requires_verification')->default(false);
            }
        });

        // Add approval and verification fields to users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_approved')) {
                $table->boolean('is_approved')->default(true); // Default true unless setting overrides
            }
            if (!Schema::hasColumn('users', 'verification_status')) {
                $table->enum('verification_status', ['none', 'pending', 'approved', 'rejected'])->default('none');
            }
            if (!Schema::hasColumn('users', 'verification_documents')) {
                $table->json('verification_documents')->nullable(); // Store paths to uploaded docs
            }
            if (!Schema::hasColumn('users', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('requires_verification');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_approved', 'verification_status', 'verification_documents', 'rejection_reason']);
        });
    }
};
