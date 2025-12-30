<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = setting('all'); // Mock retrieval, assumes a Setting model or config file wrapper
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Save settings to DB or JSON file
        // For now, this is a placeholder implementation as 'setting()' helper was a mock.
        // Real implementation would involve: Setting::updateOrCreate(['key' => $key], ['value' => $val]);

        // Example:
        // Setting::set('manual_user_approval', $request->has('manual_user_approval'));
        // Setting::set('service_commission_rate', $request->service_commission_rate);

        return redirect()->back()->with('success', 'Settings updated successfully');
    }
}
