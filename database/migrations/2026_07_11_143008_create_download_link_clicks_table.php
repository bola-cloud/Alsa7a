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
        Schema::create('download_link_clicks', function (Blueprint $table) {
            $table->id();
            $table->string('link_type')->default('download')->comment('download or general');
            $table->string('ip_address')->nullable();
            $table->string('country')->nullable();
            $table->string('os_type')->nullable()->comment('Android, iOS, Desktop, etc');
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('download_link_clicks');
    }
};
