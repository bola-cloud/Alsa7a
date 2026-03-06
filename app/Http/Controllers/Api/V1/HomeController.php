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

        $popularLeagues = League::with([
            'videos' => function ($q) {
                $q->limit(9);
            }
        ])->where('is_active', true)->take(6)->get();

        $topClubs = Club::where('is_featured', true)->with('media')->take(8)->get();

        // Only return users belonging to the 'Player' category (category_id = 1)
        $topPlayers = User::with('subscription')
            ->where('category_id', 1)
            ->where('is_featured', true)
            ->select('id', 'name', 'profile_title', 'bio', 'city', 'profile_photo_path')
            ->take(8)
            ->get();

        // Transform top players to ensure full image URL
        $topPlayers->transform(function ($player) {
            $player->image = $player->profile_photo_url;
            if ($player->profile_photo_path) {
                $url = url('storage/' . $player->profile_photo_path);
                $player->image = $url;
                $player->profile_photo_url = $url;
            }

            $player->subscription = [
                'is_subscribed' => $player->isSubscribed(),
                'type' => $player->subscription ? $player->subscription->type : null,
                'end_date' => $player->subscription ? $player->subscription->end_date : null,
                'status' => $player->subscription ? $player->subscription->status : null,
            ];

            return $player;
        });

        // featured services omitted from this response (not part of mobile home spec)

        // fetch sliders (ads) to show in the home hero/ads slider
        $sliders = Slider::orderBy('id', 'asc')->get();

        // Return the home payload in the order requested by the design
        // Fetch Leagues with their Clubs
        // Fetch Leagues with their Clubs
        $leagues = \App\Models\League::with([
            'clubs' => function ($q) {
                // $q->where('active', true)->take(10); // Club has no active column
                $q->take(10);
            }
        ])->where('is_active', true)->get();

        // Populate response data from fetched variables
        $categories = $sports;
        $featuredClubs = $topClubs;

        // featured services
        $featuredServices = Service::with(['provider', 'sport', 'club', 'media'])->where('is_featured', true)->where('is_active', true)->take(10)->get();

        // Transform featured services to include a primary image URL
        $featuredServices->transform(function ($service) {
            $service->image = null;
            if ($service->media->count() > 0) {
                $service->image = $service->media->first()->full_url;
            }
            return $service;
        });


        return response()->json([
            'status' => true,
            'data' => [
                'sliders' => $sliders,
                'categories' => $categories,
                'featured_clubs' => $featuredClubs,
                'featured_events' => $featuredEvents,
                'top_players' => $topPlayers,
                'featured_services' => $featuredServices,
                'leagues' => $leagues,
            ],
            'message' => 'Home data retrieved successfully'
        ]);
    }
}
