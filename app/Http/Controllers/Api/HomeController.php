<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sport;
use App\Models\Event;
use App\Models\League;
use App\Models\Club;
use App\Models\Service;
use App\Models\User;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // sports list
        $sports = Sport::select('id','name','slug','icon_url')->orderBy('name')->get();

        // featured events
        $featuredEvents = Event::with('club')->where('is_featured', true)->orderBy('start_at', 'asc')->take(6)->get();

        // popular leagues with one video (if exists)
        $popularLeagues = League::with(['videos' => function($q){ $q->limit(3); }])->where('is_active', true)->take(6)->get();

        // featured clubs
        $topClubs = Club::where('is_featured', true)->with('media')->take(8)->get();

        // top providers (users marked is_featured)
        $topProviders = User::where('is_featured', true)->select('id','name','profile_title','bio','city','profile_photo_path')->take(8)->get();

        // featured services
        $featuredServices = Service::with('media','provider')->take(8)->get();

        return response()->json([
            'sports' => $sports,
            'featured_events' => $featuredEvents,
            'popular_leagues' => $popularLeagues,
            'top_clubs' => $topClubs,
            'top_providers' => $topProviders,
            'featured_services' => $featuredServices,
        ]);
    }
}
