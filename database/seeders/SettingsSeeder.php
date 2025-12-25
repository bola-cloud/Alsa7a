<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_logo',
                'value' => null,
                'type' => 'image',
                'group' => 'general',
                'label' => 'Site Logo',
            ],
            [
                'key' => 'site_favicon',
                'value' => null,
                'type' => 'image',
                'group' => 'general',
                'label' => 'Favicon',
            ],
            [
                'key' => 'site_name_en',
                'value' => 'Alsa7a',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Site Name (English)',
            ],
            [
                'key' => 'site_name_ar',
                'value' => 'الساحة',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Site Name (Arabic)',
            ],
            [
                'key' => 'contact_email',
                'value' => 'info@alsa7a.com',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Contact Email',
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
