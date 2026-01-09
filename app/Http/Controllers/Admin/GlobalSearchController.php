<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Sport;
use App\Models\Club;
use App\Models\Event;
use App\Models\News;
use App\Models\Service;
use App\Models\User;
use App\Models\League;
use App\Models\Slider;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];

        // Categories
        $categories = Category::where('name_en', 'LIKE', "%{$query}%")
            ->orWhere('name_ar', 'LIKE', "%{$query}%")
            ->orWhere('description_en', 'LIKE', "%{$query}%")
            ->orWhere('description_ar', 'LIKE', "%{$query}%")
            ->limit(3)->get();
        foreach ($categories as $category) {
            $results[] = [
                'title' => $category->name, // Magic accessor
                'type' => __('admin.menu.categories'),
                'url' => route('admin.categories.edit', $category->id),
                'icon' => 'la la-list'
            ];
        }

        // Sports
        $sports = Sport::where('name_en', 'LIKE', "%{$query}%")
            ->orWhere('name_ar', 'LIKE', "%{$query}%")
            ->orWhere('name', 'LIKE', "%{$query}%")
            ->limit(3)->get();
        foreach ($sports as $sport) {
            $results[] = [
                'title' => $sport->name,
                'type' => __('admin.menu.sports'),
                'url' => route('admin.sports.edit', $sport->id),
                'icon' => 'la la-trophy'
            ];
        }

        // Clubs
        $clubs = Club::where('name_en', 'LIKE', "%{$query}%")
            ->orWhere('name_ar', 'LIKE', "%{$query}%")
            ->orWhere('description_en', 'LIKE', "%{$query}%")
            ->orWhere('description_ar', 'LIKE', "%{$query}%")
            ->limit(3)->get();
        foreach ($clubs as $club) {
            $results[] = [
                'title' => $club->name,
                'type' => __('admin.buttons.view') . ' Club', // TODO: Localize 'Club'
                'url' => route('admin.clubs.edit', $club->id),
                'icon' => 'la la-users'
            ];
        }

        // Events
        $events = Event::where('title_en', 'LIKE', "%{$query}%")
            ->orWhere('title_ar', 'LIKE', "%{$query}%")
            ->orWhere('description_en', 'LIKE', "%{$query}%")
            ->orWhere('description_ar', 'LIKE', "%{$query}%")
            ->limit(3)->get();
        foreach ($events as $event) {
            $results[] = [
                'title' => $event->title,
                'type' => __('admin.events.index'),
                'url' => route('admin.events.edit', $event->id),
                'icon' => 'la la-calendar'
            ];
        }

        // Leagues
        $leagues = League::where('name_en', 'LIKE', "%{$query}%")
            ->orWhere('name_ar', 'LIKE', "%{$query}%")
            ->orWhere('description_en', 'LIKE', "%{$query}%")
            ->orWhere('description_ar', 'LIKE', "%{$query}%")
            ->limit(3)->get();
        foreach ($leagues as $league) {
            $results[] = [
                'title' => $league->name,
                'type' => __('admin.leagues.index'),
                'url' => route('admin.leagues.edit', $league->id),
                'icon' => 'la la-trophy'
            ];
        }

        // Sliders
        $sliders = Slider::where('title_en', 'LIKE', "%{$query}%")
            ->orWhere('title_ar', 'LIKE', "%{$query}%")
            ->limit(3)->get();
        foreach ($sliders as $slider) {
            $results[] = [
                'title' => $slider->title,
                'type' => __('admin.sliders.index'),
                'url' => route('admin.sliders.edit', $slider->id),
                'icon' => 'la la-image'
            ];
        }

        // Services (No Translatable)
        $services = Service::where('title', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->limit(3)->get();
        foreach ($services as $service) {
            $results[] = [
                'title' => $service->title,
                'type' => __('admin.services.title'),
                'url' => route('admin.services.show', $service->id), // Services usually have show/index
                'icon' => 'la la-briefcase'
            ];
        }

        // News
        $news = News::where('title_en', 'LIKE', "%{$query}%")
            ->orWhere('title_ar', 'LIKE', "%{$query}%")
            ->limit(3)->get();
        foreach ($news as $item) {
            $results[] = [
                'title' => $item->title, // News uses 'title', ensure Translatable handles it (or create accessor?)
                'type' => __('admin.news.title'),
                'url' => route('admin.news.edit', $item->id),
                'icon' => 'la la-newspaper-o'
            ];
        }

        // Users
        $users = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->limit(3)->get();
        foreach ($users as $user) {
            $results[] = [
                'title' => $user->name,
                'type' => __('admin.dashboard.users'),
                'url' => route('admin.users.show', $user->id),
                'icon' => 'la la-user'
            ];
        }

        return response()->json($results);
    }
}
