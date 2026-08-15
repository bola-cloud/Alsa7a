<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The country list must cover every market before the mobile update
     * forces users to pick one. Oman, Saudi, UAE and Egypt already exist —
     * this adds the remaining GCC states.
     *
     * Prices/currency mirror the existing rows (billing goes through Thawani
     * in OMR regardless of the user's country).
     */
    protected array $countries = [
        ['name_ar' => 'الكويت', 'name_en' => 'Kuwait', 'code' => 'KW'],
        ['name_ar' => 'قطر', 'name_en' => 'Qatar', 'code' => 'QA'],
        ['name_ar' => 'البحرين', 'name_en' => 'Bahrain', 'code' => 'BH'],
    ];

    public function up(): void
    {
        foreach ($this->countries as $country) {
            if (DB::table('countries')->where('code', $country['code'])->exists()) {
                continue;
            }

            DB::table('countries')->insert($country + [
                'flag' => null,
                'is_active' => 1,
                'subscription_monthly_price' => '5.000',
                'subscription_annual_price' => '50.000',
                'currency' => 'OMR',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Only removes the rows if nothing was ever assigned to them, so a
     * rollback can never orphan real user or content data.
     */
    public function down(): void
    {
        foreach ($this->countries as $country) {
            $row = DB::table('countries')->where('code', $country['code'])->first();

            if (! $row) {
                continue;
            }

            $inUse = DB::table('users')->where('country_id', $row->id)->exists()
                || DB::table('posts')->where('country_id', $row->id)->exists();

            if (! $inUse) {
                DB::table('countries')->where('id', $row->id)->delete();
            }
        }
    }
};
