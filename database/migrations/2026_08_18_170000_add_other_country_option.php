<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * A catch-all country so the ~69% of users whose real country is not one
     * of the seven supported markets still have something to pick. They end
     * up grouped together and isolated from the country-specific markets,
     * instead of being forced to claim a country that is not theirs.
     *
     * 'ZZ' is the ISO 3166-1 user-assigned code for "unknown/unspecified".
     */
    public function up(): void
    {
        if (DB::table('countries')->where('code', 'ZZ')->exists()) {
            return;
        }

        DB::table('countries')->insert([
            'name_ar' => 'دولة أخرى',
            'name_en' => 'Other',
            'code' => 'ZZ',
            'flag' => null,
            'is_active' => 1,
            'subscription_monthly_price' => '5.000',
            'subscription_annual_price' => '50.000',
            'currency' => 'OMR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Only removed if nobody selected it, so a rollback can never orphan
     * real users or their content.
     */
    public function down(): void
    {
        $row = DB::table('countries')->where('code', 'ZZ')->first();

        if (! $row) {
            return;
        }

        $inUse = DB::table('users')->where('country_id', $row->id)->exists()
            || DB::table('posts')->where('country_id', $row->id)->exists();

        if (! $inUse) {
            DB::table('countries')->where('id', $row->id)->delete();
        }
    }
};
