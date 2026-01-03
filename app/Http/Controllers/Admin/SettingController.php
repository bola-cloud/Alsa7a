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
        $inputs = $request->except('_token');

        foreach ($inputs as $key => $value) {
            $setting = \App\Models\Setting::where('key', $key)->first();

            if ($setting) {
                if ($request->hasFile($key)) {
                    // Handle File Upload
                    $path = $request->file($key)->store('settings', 'public'); // Store in storage/app/public/settings
                    $setting->value = 'storage/' . $path; // or just $path if you use accessor handles 'storage/'
                    // Note: In Setting model accessor, I used 'storage/' prefix, so here I should store relative path? 
                    // Let's check Accessor: return asset('storage/' . $this->value);
                    // If I store 'settings/filename.jpg', it becomes asset('storage/settings/filename.jpg'). Correct.
                    $setting->value = $path;
                } else {
                    $setting->value = $value;
                }
                $setting->save();
            }
        }

        return redirect()->back()->with('success', 'Settings updated successfully');
    }
}
