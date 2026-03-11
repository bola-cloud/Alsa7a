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
use App\Traits\FormatsProfileData;

class HomeController extends Controller
{
    use FormatsProfileData;

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

        // Returning full user details with relations for proper profile formatting
        $topPlayers = User::with(['subscription', 'category', 'club', 'ownedClub'])
            ->where('category_id', 1)
            ->where('is_featured', true)
            ->take(8)
            ->get();

        $currentUser = auth('sanctum')->user();

        // Transform top players to ensure full image URL and profile formatting
        $topPlayers->transform(function ($player) use ($currentUser) {
            $player->image = $player->profile_photo_url;
            if ($player->profile_photo_path) {
                $url = url('storage/' . $player->profile_photo_path);
                $player->image = $url;
                $player->profile_photo_url = $url;
            }

            $profileData = $this->getProfileData($player, false, $currentUser);
            foreach ($profileData as $key => $value) {
                if (!is_array($player->{$key})) {
                    $player->{$key} = $value;
                }
            }

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
        $featuredServices = Service::with(['provider.category', 'provider.club', 'provider.ownedClub', 'sport', 'club', 'media'])->where('is_featured', true)->where('is_active', true)->take(10)->get();

        // Unique providers processing
        $providersToProcess = collect();
        foreach ($featuredServices as $service) {
            if ($service->provider) $providersToProcess->put($service->provider->id, $service->provider);
        }

        $providersToProcess->each(function ($provider) use ($currentUser) {
            if (is_object($provider)) {
                $profileData = $this->getProfileData($provider, false, $currentUser);
                foreach ($profileData as $key => $value) {
                    if (!is_array($provider->{$key})) {
                        $provider->{$key} = $value;
                    }
                }
            }
        });

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
