<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');

        // Enrich with full URLs for images
        $siteLogo = Setting::where('key', 'site_logo')->first();
        $siteIcon = Setting::where('key', 'site_icon')->first();

        $response = [
            'site_name' => $settings['site_name'] ?? 'Alsa7a',
            'site_logo' => $siteLogo ? $siteLogo->image_url : asset('app-assets/images/logo.jpeg'),
            'site_icon' => $siteIcon ? $siteIcon->image_url : asset('app-assets/images/ico/apple-icon-120.png'),
            'service_commission' => $settings['service_commission'] ?? 10,
            'currency' => 'OMR', // Fixed to OMR for all project
        ];

        return response()->json([
            'status' => true,
            'message' => 'Settings retrieved successfully',
            'data' => $response
        ]);
    }
}
