<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserActivityController extends Controller
{
    public function index()
    {
        $activities = \App\Models\UserActivity::with('user')
            ->latest()
            ->paginate(request('per_page', 15));
            
        return view('admin.user_activities.index', compact('activities'));
    }
}
