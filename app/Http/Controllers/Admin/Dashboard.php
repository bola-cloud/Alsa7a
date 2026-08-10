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

        if ($adminCountryId && $adminCountryId !== 'all') {
            $requestsQuery->whereHas('requester', function ($q) use ($adminCountryId) {
                $q->where('country_id', $adminCountryId);
            });
            $ticketsQuery->whereHas('user', function ($q) use ($adminCountryId) {
                $q->where('country_id', $adminCountryId);
            });
        }

        $stats = [
            'users' => User::count(),
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
