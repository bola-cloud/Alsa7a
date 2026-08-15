<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Adds the admin toggle that switches the home feed (and reels) between
     * the personalised ordering and plain newest-first.
     *
     * Default is 'latest' because that is the behaviour the client asked for;
     * flipping it to 'algorithmic' restores the previous feed logic.
     */
    public function up(): void
    {
        if (! DB::table('settings')->where('key', 'feed_sort_mode')->exists()) {
            DB::table('settings')->insert([
                'key' => 'feed_sort_mode',
                'value' => 'latest',
                'type' => 'select',
                'group' => 'general',
                'label' => 'admin.settings.feed_sort_mode',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'feed_sort_mode')->delete();
    }
};
