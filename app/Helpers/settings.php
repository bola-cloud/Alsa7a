<?php

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        if ($key === 'all') {
            // Fetch all settings and group them
            $settings = \App\Models\Setting::all();

            $grouped = [];
            foreach ($settings as $setting) {
                // Add select options logic if needed
                if ($setting->type === 'select' && $setting->key === 'manual_user_approval') {
                    $setting->options = [0 => 'admin.categories.no', 1 => 'admin.categories.yes'];
                }

                // Add image_url logic
                if ($setting->type === 'image') {
                    // Check if value is a path or full URL (mock data uses asset())
                    if (filter_var($setting->value, FILTER_VALIDATE_URL)) {
                        $setting->image_url = $setting->value;
                    } else {
                        // Assuming uploaded files are in storage or public
                        $setting->image_url = asset($setting->value);
                    }
                }

                $grouped[$setting->group][] = $setting;
            }

            return $grouped;
        }

        // Fetch single setting
        // In production, use Cache::remember
        $setting = \App\Models\Setting::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return $setting->value;
    }
}
