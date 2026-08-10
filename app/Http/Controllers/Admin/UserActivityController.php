<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserActivityController extends Controller
{
    public function index()
    {
        $query = \App\Models\UserActivity::with('user')->latest();

        $adminCountryId = session('admin_country_id');
        if ($adminCountryId && $adminCountryId !== 'all') {
            $query->whereHas('user', function($q) use ($adminCountryId) {
                $q->where(function ($sub) use ($adminCountryId) {
                    $sub->where('country_id', $adminCountryId)
                        ->orWhereNull('country_id');
                });
            });
        }

        $activities = $query->paginate(request('per_page', 15));
            
        return view('admin.user_activities.index', compact('activities'));
    }
}
