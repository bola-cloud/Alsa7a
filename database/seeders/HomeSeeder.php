<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sport;
use App\Models\Club;
use App\Models\Team;
use App\Models\League;
use App\Models\LeagueVideo;
use App\Models\Event;
use App\Models\Media;
use App\Models\Service;
use App\Models\ServiceMedia;
use App\Models\User;
use App\Models\Slider;
use Illuminate\Support\Str;

class HomeSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Sports
        $football = Sport::firstOrCreate(
            ['slug' => 'football'],
            ['name' => 'Football', 'name_en' => 'Football', 'name_ar' => 'كرة القدم', 'icon_url' => 'images-demo/soccer-ball.png']
        );
        $basketball = Sport::firstOrCreate(
            ['slug' => 'basketball'],
            ['name' => 'Basketball', 'name_en' => 'Basketball', 'name_ar' => 'كرة السلة', 'icon_url' => 'images-demo/basketball.png']
        );
        $cricket = Sport::firstOrCreate(
            ['slug' => 'cricket'],
            ['name' => 'Cricket', 'name_en' => 'Cricket', 'name_ar' => 'الكريكيت', 'icon_url' => 'images-demo/cricket.png']
        );

        // 2. Create Clubs
        // SQL: 1=Madreed, 2=Barcelona, 3=Al-Ahly
        $club1 = Club::firstOrCreate(
            ['slug' => 'madreed-club'],
            ['name' => 'Madreed Club', 'name_en' => 'Madreed Club', 'name_ar' => 'نادي مدريد', 'city' => 'Madrid', 'country' => 'Spain', 'logo_url' => 'images-demo/club1.png', 'is_featured' => true]
        );
        $club2 = Club::firstOrCreate(
            ['slug' => 'barcelona-club'],
            ['name' => 'Barcelona Club', 'name_en' => 'Barcelona Club', 'name_ar' => 'نادي برشلونة', 'city' => 'Barcelona', 'country' => 'Spain', 'logo_url' => 'images-demo/club2.jpg', 'is_featured' => true]
        );
        $club3 = Club::firstOrCreate(
            ['slug' => 'al-ahly-club'],
            ['name' => 'Al-Ahly Club', 'name_en' => 'Al-Ahly Club', 'name_ar' => 'النادي الاهلي ', 'city' => 'Cairo', 'country' => 'Egypt', 'logo_url' => 'images-demo/club3.jpg', 'is_featured' => false]
        );

        // Link clubs to sports
        $club1->sports()->syncWithoutDetaching([$football->id]);
        $club2->sports()->syncWithoutDetaching([$football->id]);
        $club3->sports()->syncWithoutDetaching([$football->id]);

        // 3. Create Teams
        // SQL: 1=Madreed FC (Club 1), 2=Al-Nasr FC (Club 2 - wait, SQL says club_id 2 for Al-Nasr FC)
        // Note: SQL `teams` dump had: (2, 2, 1, 'Al-Nasr FC'...). Club 2 is Barcelona. So Al-Nasr FC is linked to Barcelona Club in the dump?
        // We will follow the dump exactly even if it seems odd, or user customized it.
        $team1 = Team::firstOrCreate(['slug' => 'madreed-fc'], ['club_id' => $club1->id, 'sport_id' => $football->id, 'name' => 'Madreed FC']);
        $team2 = Team::firstOrCreate(['slug' => 'al-nasr-fc'], ['club_id' => $club2->id, 'sport_id' => $football->id, 'name' => 'Al-Nasr FC']);

        // 4. Create Leagues
        $league1 = League::firstOrCreate(['slug' => 'champion-league'], [
            'sport_id' => $football->id,
            'name' => 'Champion League',
            'name_en' => 'Champion League',
            'name_ar' => 'دوري الأبطال',
            'image' => 'images-demo/popular-leagues2.png',
            'season' => '2025/2026'
        ]);
        $league2 = League::firstOrCreate(['slug' => 'basket-league'], [
            'sport_id' => $basketball->id,
            'name' => 'Basket League',
            'name_en' => 'Basket League',
            'name_ar' => 'دوري السلة',
            'image' => 'images-demo/popular-leagues.png',
            'season' => '2025'
        ]);

        $league1->teams()->syncWithoutDetaching([$team1->id, $team2->id]);

        // League Videos
        LeagueVideo::firstOrCreate(['league_id' => $league1->id, 'title' => 'Al-Nasr vs Madreed Highlights'], ['url' => 'https://placehold.co/640x360/video.mp4', 'duration_seconds' => 262]);
        LeagueVideo::firstOrCreate(['league_id' => $league1->id, 'title' => 'Top Goals'], ['url' => 'https://placehold.co/640x360/video2.mp4', 'duration_seconds' => 120]);

        // 5. Create Events
        Event::firstOrCreate(['slug' => 'madreed-vs-al-nasr'], [
            'club_id' => $club1->id,
            'sport_id' => $football->id,
            'title' => 'Madreed vs Al-Nasr',
            'title_en' => 'Madreed vs Al-Nasr',
            'title_ar' => 'مدريد ضد النصر',
            'description' => 'Championship match',
            'description_en' => 'Championship match',
            'description_ar' => 'مباراة البطولة',
            'start_at' => '2025-11-30 01:23:44',
            'venue' => 'Main Stadium',
            'price' => 25.00,
            'is_featured' => true,
            'featured_image' => 'images-demo/events.png'
        ]);

        Event::firstOrCreate(['slug' => 'al-mussanah-friendly'], [
            'club_id' => $club3->id,
            'sport_id' => $football->id,
            'title' => 'Al-Mussanah Friendly',
            'title_en' => 'Al-Mussanah Friendly',
            'title_ar' => 'ودية الموسنة',
            'description' => 'Friendly match',
            'description_en' => 'Friendly match',
            'description_ar' => 'مباراة ودية',
            'start_at' => '2025-11-26 01:23:44',
            'venue' => 'City Arena',
            'price' => 10.00,
            'is_featured' => true,
            'featured_image' => 'images-demo/events.png'
        ]);

        // 6. Sliders
        Slider::firstOrCreate(['title' => 'Football Champion League Celebration'], ['title_en' => 'Football Champion League Celebration', 'title_ar' => 'احتفال دوري أبطال الكرة', 'image' => 'images-demo/slider.png']);
        Slider::firstOrCreate(['title' => 'Basketball Championship Highlights'], ['title_en' => 'Basketball Championship Highlights', 'title_ar' => 'أبرز أحداث بطولة السلة', 'image' => 'images-demo/slider.png']);
        Slider::firstOrCreate(['title' => 'Join Our Training Camps'], ['title_en' => 'Join Our Training Camps', 'title_ar' => 'انضم إلى معسكراتنا التدريبية', 'image' => 'images-demo/slider.png']);
    }
}
