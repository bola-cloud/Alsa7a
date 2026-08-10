<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminCountryController extends Controller
{
    /**
     * Set the globally selected country for the Admin Panel session.
     */
    public function setCountry(Request $request)
    {
        $request->validate([
            'admin_country_id' => 'required'
        ]);

        $countryId = $request->input('admin_country_id');

        // Store the selected country ID (or 'all') in the session
        session(['admin_country_id' => $countryId]);

        return redirect()->back()->with('success', 'تم تغيير الدولة بنجاح.');
    }
}
