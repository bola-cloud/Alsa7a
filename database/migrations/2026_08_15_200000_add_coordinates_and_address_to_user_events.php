<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Calendar events get a real map pin plus a written address.
     *
     * Coordinates go into proper decimal columns rather than the
     * "Lat: x, Lng: y" string the services table uses, so they can be
     * validated and, later, queried by distance. The existing `location`
     * column stays untouched for backward compatibility.
     */
    public function up(): void
    {
        Schema::table('user_events', function (Blueprint $table) {
            // 10,7 covers the full range with ~1cm precision (-180.0000000).
            $table->decimal('latitude', 10, 7)->nullable()->after('location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('address')->nullable()->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_events', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'address']);
        });
    }
};
