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
        // create sports
        // use existing demo images from SQL dump
        $football = Sport::create(['name' => 'Football', 'name_en' => 'Football', 'name_ar' => 'كرة القدم', 'slug' => 'football', 'icon_url' => 'images-demo/football.png']);
        $basketball = Sport::create(['name' => 'Basketball', 'name_en' => 'Basketball', 'name_ar' => 'كرة السلة', 'slug' => 'basketball', 'icon_url' => 'images-demo/basket.png']);
        $cricket = Sport::create(['name' => 'Cricket', 'name_en' => 'Cricket', 'name_ar' => 'الكريكيت', 'slug' => 'cricket', 'icon_url' => 'images-demo/cricket.png']);

        // create clubs
        $club1 = Club::create([ 'name' => 'Madreed Club', 'name_en' => 'Madreed Club', 'name_ar' => 'نادي مدريد', 'slug' => 'madreed-club', 'city' => 'Madrid', 'country' => 'Spain', 'logo_url' => 'https://placehold.co/200x200', 'is_featured' => true, 'meta' => [] ]);
        $club2 = Club::create([ 'name' => 'Al-Nasr Club', 'name_en' => 'Al-Nasr Club', 'name_ar' => 'نادي النصر', 'slug' => 'al-nasr', 'city' => 'Riyadh', 'country' => 'Saudi Arabia', 'logo_url' => 'https://placehold.co/200x200', 'is_featured' => true, 'meta' => [] ]);
        $club3 = Club::create([ 'name' => 'Al-Mussanah Club', 'name_en' => 'Al-Mussanah Club', 'name_ar' => 'نادي المضيضة', 'slug' => 'al-mussanah', 'city' => 'Muscat', 'country' => 'Oman', 'logo_url' => 'https://placehold.co/200x200', 'is_featured' => false, 'meta' => [] ]);

        // link clubs to sports
        $club1->sports()->attach($football->id);
        $club1->sports()->attach($basketball->id);
        $club2->sports()->attach($football->id);
        $club3->sports()->attach($football->id);

        // create teams (each team belongs to a club and a sport)
        $team1 = Team::create(['club_id' => $club1->id, 'sport_id' => $football->id, 'name' => 'Madreed FC', 'slug' => 'madreed-fc']);
        $team2 = Team::create(['club_id' => $club2->id, 'sport_id' => $football->id, 'name' => 'Al-Nasr FC', 'slug' => 'al-nasr-fc']);

        // create leagues
        $league1 = League::create(['sport_id' => $football->id, 'name' => 'Champion League', 'name_en' => 'Champion League', 'name_ar' => 'دوري الأبطال', 'slug' => 'champion-league', 'season' => '2025/2026']);
        $league2 = League::create(['sport_id' => $basketball->id, 'name' => 'Basket League', 'name_en' => 'Basket League', 'name_ar' => 'دوري السلة', 'slug' => 'basket-league', 'season' => '2025']);

        // attach teams to leagues
        $league1->teams()->attach($team1->id);
        $league1->teams()->attach($team2->id);

        // add league videos
        LeagueVideo::create(['league_id' => $league1->id, 'title' => 'Al-Nasr vs Madreed Highlights', 'url' => 'https://placehold.co/640x360/video.mp4', 'duration_seconds' => 262]);
        LeagueVideo::create(['league_id' => $league1->id, 'title' => 'Top Goals', 'url' => 'https://placehold.co/640x360/video2.mp4', 'duration_seconds' => 120]);

        // add events
        // match SQL dump: both events use demo image path
        Event::create(['club_id' => $club1->id, 'sport_id' => $football->id, 'title' => 'Madreed vs Al-Nasr', 'title_en' => 'Madreed vs Al-Nasr', 'title_ar' => 'مدريد ضد النصر', 'slug' => 'madreed-vs-al-nasr', 'description' => 'Championship match', 'description_en' => 'Championship match', 'description_ar' => 'مباراة البطولة', 'start_at' => now()->addDays(7), 'venue' => 'Main Stadium', 'price' => 25.00, 'is_featured' => true, 'featured_image' => 'images-demo/events.png']);
        Event::create(['club_id' => $club3->id, 'sport_id' => $football->id, 'title' => 'Al-Mussanah Friendly', 'title_en' => 'Al-Mussanah Friendly', 'title_ar' => 'ودية الموسنة', 'slug' => 'al-mussanah-friendly', 'description' => 'Friendly match', 'description_en' => 'Friendly match', 'description_ar' => 'مباراة ودية', 'start_at' => now()->addDays(3), 'venue' => 'City Arena', 'price' => 10.00, 'is_featured' => true, 'featured_image' => 'images-demo/events.png']);

        // add media for clubs
        Media::create(['mediaable_type' => Club::class, 'mediaable_id' => $club1->id, 'url' => 'https://placehold.co/600x300', 'type' => 'image', 'title' => 'Club banner']);
        Media::create(['mediaable_type' => Club::class, 'mediaable_id' => $club2->id, 'url' => 'https://placehold.co/600x300', 'type' => 'image', 'title' => 'Club banner']);

        // create a few users as providers / postulants
        $provider1 = User::create(['name' => 'Heba Abdelmonem', 'email' => 'provider1@example.com', 'password' => bcrypt('password'), 'category_id' => null, 'is_featured' => true, 'profile_title' => 'Football Player', 'bio' => 'Experienced midfielder', 'city' => 'Cairo', 'profile_photo_path' => 'images-demo/players.png']);
        $provider2 = User::create(['name' => 'Marco H', 'email' => 'provider2@example.com', 'password' => bcrypt('password'), 'category_id' => null, 'is_featured' => true, 'profile_title' => 'Goalkeeper', 'bio' => 'Top keeper', 'city' => 'Madrid', 'profile_photo_path' => 'images-demo/players.png']);

        // create services for providers
        $service1 = Service::create(['provider_id' => $provider1->id, 'title' => 'Personal Coaching', 'slug' => Str::slug('Personal Coaching'), 'description' => 'One-on-one coaching session', 'price' => 21.89, 'duration_minutes' => 60, 'currency' => 'USD']);
        ServiceMedia::create(['service_id' => $service1->id, 'url' => 'https://placehold.co/600x300', 'type' => 'image', 'title' => 'Coaching photo']);

        // additional featured services/events to show up on home
        Service::create(['provider_id' => $provider2->id, 'title' => 'Goalkeeping Clinic', 'slug' => Str::slug('Goalkeeping Clinic'), 'description' => 'Group clinic', 'price' => 15.00, 'duration_minutes' => 120, 'currency' => 'USD']);

        // demo sliders (ads) for home hero — use same image path as SQL dump
        Slider::create(['title' => 'Football Champion League Celebration', 'title_en' => 'Football Champion League Celebration', 'title_ar' => 'احتفال دوري أبطال الكرة', 'image' => 'images-demo/slider.png']);
        Slider::create(['title' => 'Basketball Championship Highlights', 'title_en' => 'Basketball Championship Highlights', 'title_ar' => 'أبرز أحداث بطولة السلة', 'image' => 'images-demo/slider.png']);
        Slider::create(['title' => 'Join Our Training Camps', 'title_en' => 'Join Our Training Camps', 'title_ar' => 'انضم إلى معسكراتنا التدريبية', 'image' => 'images-demo/slider.png']);

    }
}
