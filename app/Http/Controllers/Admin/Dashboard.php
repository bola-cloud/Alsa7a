<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\ServiceRequest;
use App\Models\Post;
use App\Models\News;
use App\Models\Ticket;
use App\Models\Service;

class Dashboard extends Controller
{
    public function index(Request $request)
    {
        $adminCountryId = session('admin_country_id');

        $requestsQuery = ServiceRequest::query();
        $ticketsQuery = Ticket::query();

        // ServiceRequest/Ticket carry no country_id of their own, so the
        // country is inherited through the requester/user relation. Strict
        // on purpose, matching the rest of the admin country switch: picking
        // a country isolates that country's data instead of blending in
        // every user who has none set yet.
        if ($adminCountryId && $adminCountryId !== 'all') {
            $requestsQuery->whereHas('requester', function ($q) use ($adminCountryId) {
                $adminCountryId === 'none'
                    ? $q->whereNull('country_id')
                    : $q->where('country_id', $adminCountryId);
            });
            $ticketsQuery->whereHas('user', function ($q) use ($adminCountryId) {
                $adminCountryId === 'none'
                    ? $q->whereNull('country_id')
                    : $q->where('country_id', $adminCountryId);
            });
        }

        $stats = [
            // User has no automatic country scope (see App\Models\User), so
            // it is filtered explicitly here, same as Admin\UserController.
            'users' => User::inCountry($adminCountryId)->count(),
            'services' => Service::count(),
            'requests' => $requestsQuery->count(),
            'posts' => Post::count(),
            'news' => News::count(),
            'tickets' => $ticketsQuery->count(),
        ];

        // dynamic progress calculation (assuming target of 100 for demo, or relative to max)
        $target = 20; // Example target to show progress
        $percentages = [];
        foreach ($stats as $key => $value) {
            $percent = ($value / $target) * 100;
            $percentages[$key] = $percent > 100 ? 100 : $percent;
        }

        return view('admin.dashboard', compact('stats', 'percentages'));
    }
}
