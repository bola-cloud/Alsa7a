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
        $stats = [
            'users' => User::count(),
            'services' => Service::count(),
            'requests' => ServiceRequest::count(),
            'posts' => Post::count(),
            'news' => News::count(),
            'tickets' => Ticket::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
