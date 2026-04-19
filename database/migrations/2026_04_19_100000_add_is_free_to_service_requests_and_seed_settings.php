<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add is_free to service_requests
        Schema::table('service_requests', function (Blueprint $table) {
            $table->boolean('is_free')->default(false)->after('payment_status');
        });

        // 2. Seed default prices for the two special service types
        $settings = [
            [
                'key'   => 'performance_experience_price',
                'value' => '1',
                'type'  => 'number',
                'group' => 'services',
                'label' => 'سعر خدمة تجربة الأداء (ريال عماني)',
            ],
            [
                'key'   => 'loan_request_price',
                'value' => '1',
                'type'  => 'number',
                'group' => 'services',
                'label' => 'سعر خدمة طلب الإعارة (ريال عماني)',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('is_free');
        });

        DB::table('settings')->whereIn('key', [
            'performance_experience_price',
            'loan_request_price',
        ])->delete();
    }
};
