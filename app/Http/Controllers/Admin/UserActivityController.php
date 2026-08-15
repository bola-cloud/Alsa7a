<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserActivityController extends Controller
{
    public function index()
    {
        $query = \App\Models\UserActivity::with('user')->latest();

        // UserActivity carries no country_id of its own, so the country is
        // inherited through the user relation. Strict on purpose, matching
        // the rest of the admin country switch.
        $adminCountryId = session('admin_country_id');
        if ($adminCountryId && $adminCountryId !== 'all') {
            $query->whereHas('user', function($q) use ($adminCountryId) {
                $adminCountryId === 'none'
                    ? $q->whereNull('country_id')
                    : $q->where('country_id', $adminCountryId);
            });
        }

        $activities = $query->paginate(request('per_page', 15));
            
        return view('admin.user_activities.index', compact('activities'));
    }
}
