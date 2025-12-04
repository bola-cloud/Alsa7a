<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('leagues')) {
            return;
        }

        Schema::table('leagues', function (Blueprint $table) {
            if (! Schema::hasColumn('leagues', 'image')) {
                $table->string('image')->nullable()->after('description_ar');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasTable('leagues')) {
            return;
        }

        Schema::table('leagues', function (Blueprint $table) {
            if (Schema::hasColumn('leagues', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};
