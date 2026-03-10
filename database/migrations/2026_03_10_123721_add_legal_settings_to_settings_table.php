<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $settings = [
            [
                'key' => 'terms_en',
                'value' => '<h3>Terms and Conditions</h3><p>Default terms content here...</p>',
                'type' => 'richtext',
                'group' => 'legal',
                'label' => 'admin.settings.terms_en',
            ],
            [
                'key' => 'terms_ar',
                'value' => '<h3>الشروط والأحكام</h3><p>محتوى الشروط الافتراضي هنا...</p>',
                'type' => 'richtext',
                'group' => 'legal',
                'label' => 'admin.settings.terms_ar',
            ],
            [
                'key' => 'privacy_en',
                'value' => '<h3>Privacy Policy</h3><p>Default privacy content here...</p>',
                'type' => 'richtext',
                'group' => 'legal',
                'label' => 'admin.settings.privacy_en',
            ],
            [
                'key' => 'privacy_ar',
                'value' => '<h3>سياسة الخصوصية</h3><p>محتوى سياسة الخصوصية الافتراضي هنا...</p>',
                'type' => 'richtext',
                'group' => 'legal',
                'label' => 'admin.settings.privacy_ar',
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        \App\Models\Setting::whereIn('key', ['terms_en', 'terms_ar', 'privacy_en', 'privacy_ar'])->delete();
    }
};
