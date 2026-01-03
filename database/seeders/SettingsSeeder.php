<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            [
                'key' => 'site_name',
                'value' => 'Alsa7a',
                'type' => 'text',
                'group' => 'general',
                'label' => 'admin.settings.site_name',
            ],
            [
                'key' => 'site_logo',
                'value' => 'app-assets/images/logo.jpeg',
                'type' => 'image',
                'group' => 'general',
                'label' => 'admin.settings.site_logo',
            ],
            [
                'key' => 'site_icon',
                'value' => 'app-assets/images/ico/apple-icon-120.png',
                'type' => 'image',
                'group' => 'general',
                'label' => 'admin.settings.site_icon',
            ],
            [
                'key' => 'manual_user_approval',
                'value' => '0',
                'type' => 'select', // special handling in view for select
                'group' => 'general',
                'label' => 'admin.settings.manual_user_approval',
            ],
            [
                'key' => 'service_commission',
                'value' => '10',
                'type' => 'text',
                'group' => 'commission',
                'label' => 'admin.settings.service_commission',
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
