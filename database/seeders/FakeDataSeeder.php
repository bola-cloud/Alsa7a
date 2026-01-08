<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News;
use App\Models\Post;
use App\Models\ServiceRequest;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Club;
use App\Models\Category;

class FakeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have users
        if (User::count() < 10) {
            User::factory(10)->create();
        }

        $users = User::all();
        $user = $users->first();

        // 1. News
        if (News::count() < 5) {
            News::create([
                'title_en' => 'Championship Finals',
                'title_ar' => 'نهائيات البطولة',
                'content_en' => 'The finals will be held next week.',
                'content_ar' => 'ستقام النهائيات الأسبوع المقبل.',
                'is_active' => true,
            ]);
            News::create([
                'title_en' => 'New Season Starts',
                'title_ar' => 'بداية الموسم الجديد',
                'content_en' => 'Registration is open.',
                'content_ar' => 'التسجيل مفتوح.',
                'is_active' => true,
            ]);
        }

        // 2. Posts
        if (Post::count() < 10) {
            foreach ($users as $u) {
                Post::create([
                    'user_id' => $u->id,
                    'content' => 'Just a random post content here.',
                    'type' => 'text'
                ]);
            }
        }

        // 3. Services & Requests
        if (Service::count() < 5) {
            $sport = \App\Models\Sport::first();
            if (!$sport) {
                $sport = \App\Models\Sport::create(['name' => ['en' => 'Football', 'ar' => 'كرة القدم']]);
            }

            $service = Service::create([
                'provider_id' => $user->id,
                'sport_id' => $sport->id,
                'title' => 'Football Training',
                'slug' => 'football-training-' . uniqid(),
                'description' => 'Professional training sessions.',
                'price' => 100,
                'location' => 'Cairo Stadium',
                'duration_minutes' => 60,
                'is_active' => true,
            ]);

            // Requests
            ServiceRequest::create([
                'service_id' => $service->id,
                'requester_id' => $users->last()->id,
                'provider_id' => $user->id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'price' => 100,
                'scheduled_at' => now()->addDays(2)->setHour(10)->setMinute(0),
            ]);
        }

        // 4. Tickets (Support)
        // Check if Ticket model exists properly first, usually created via package or custom
        // Assuming App\Models\Ticket exists as per Dashboard controller logic
        if (Ticket::count() < 3) {
            Ticket::create([
                'user_id' => $user->id,
                'subject' => 'Login Issue',
                'message' => 'I cannot login to my account.',
                'status' => 'open',
                'priority' => 'high'
            ]);
        }

    }
}
