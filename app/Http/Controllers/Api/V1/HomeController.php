<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sport;
use App\Models\Event;
use App\Models\League;
use App\Models\Club;
use App\Models\Service;
use App\Models\User;
use App\Models\Slider;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $sports = Sport::select('id', 'name', 'name_en', 'name_ar', 'slug', 'icon_url')->orderBy('name')->get();

        $featuredEvents = Event::with('club')->where('is_featured', true)->orderBy('start_at', 'asc')->take(6)->get();

        $popularLeagues = League::with(['videos' => function ($q) {
            $q->limit(9); }])->where('is_active', true)->take(6)->get();

        $topClubs = Club::where('is_featured', true)->with('media')->take(8)->get();

        // Only return users belonging to the 'Player' category (category_id = 1)
        $topPlayers = User::where('category_id', 1)
            ->where('is_featured', true)
            ->select('id', 'name', 'profile_title', 'bio', 'city', 'profile_photo_path')
            ->take(8)
            ->get();

        // featured services omitted from this response (not part of mobile home spec)

        // fetch sliders (ads) to show in the home hero/ads slider
        $sliders = Slider::orderBy('id', 'asc')->get();

        // Return the home payload in the order requested by the design
        return response()->json([
            'sports' => $sports,
            'slider' => $sliders,
            'popular_leagues' => $popularLeagues,
            'featured_events' => $featuredEvents,
            'top_clubs' => $topClubs,
            'top_players' => $topPlayers,
        ]);
    }
}
