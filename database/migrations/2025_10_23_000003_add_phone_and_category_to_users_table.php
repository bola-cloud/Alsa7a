<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->string('phone_verification_code', 10)->nullable()->after('phone_verified_at');
            $table->unsignedBigInteger('category_id')->nullable()->after('phone_verification_code');

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['phone', 'phone_verified_at', 'phone_verification_code', 'category_id']);
        });
    }
};
