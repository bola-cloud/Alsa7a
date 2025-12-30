<?php

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        // Mock implementation until Setting model/cache is fully set up
        // In real app: return App\Models\Setting::get($key, $default);
        $settings = [
            'manual_user_approval' => false, // Default to false (auto-approve)
        ];

        return $settings[$key] ?? $default;
    }
}
