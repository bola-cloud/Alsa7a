<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Country;
use App\Models\CommunityPost;
use App\Models\CommunityCategory;
use Illuminate\Http\Request;

class TestV2 extends Command
{
    protected $signature = 'test:v2';
    protected $description = 'Test V2 logic';

    public function handle()
    {
        $this->info("--- Starting V2 API Tests ---");

        // 1. Create dummy country and users
        $country1 = Country::firstOrCreate(['code' => 'EG'], ['name_ar' => 'مصر', 'name_en' => 'Egypt', 'is_active' => true]);
        $country2 = Country::firstOrCreate(['code' => 'SA'], ['name_ar' => 'السعودية', 'name_en' => 'Saudi Arabia', 'is_active' => true]);

        $user1 = User::factory()->create(['country_id' => $country1->id]);
        $user2 = User::factory()->create(['country_id' => $country2->id]);
        
        $this->info("Created User 1 (EG) and User 2 (SA)");

        // 2. Create Community Posts
        $cat = CommunityCategory::firstOrCreate(['name_ar' => 'Test', 'name_en' => 'Test']);

        // Authenticate User 1 to test auto-assign
        auth('sanctum')->setUser($user1);
        $post1 = CommunityPost::create(['user_id' => $user1->id, 'community_category_id' => $cat->id, 'content' => 'Egypt Post', 'processing_status' => 'completed']);
        
        auth('sanctum')->setUser($user2);
        $post2 = CommunityPost::create(['user_id' => $user2->id, 'community_category_id' => $cat->id, 'content' => 'Saudi Post', 'processing_status' => 'completed']);

        $this->info("Created Post {$post1->id} with country_id {$post1->country_id} (Expected {$user1->country_id})");
        $this->info("Created Post {$post2->id} with country_id {$post2->country_id} (Expected {$user2->country_id})");

        // 3. Test API Request without CountryScope (V1)
        $requestV1 = Request::create('http://localhost/api/v1/community/posts', 'GET');
        $requestV1->setUserResolver(function () use ($user1) { return $user1; });
        app()->instance('request', $requestV1);

        $v1PostsCount = CommunityPost::count();
        $this->info("V1 Total Posts (Should be all): {$v1PostsCount}");

        // 4. Test API Request with CountryScope (V2)
        $requestV2 = Request::create('http://localhost/api/v2/community/posts', 'GET');
        $requestV2->setUserResolver(function () use ($user1) { return $user1; });
        app()->instance('request', $requestV2);
        
        // Sanctum guard simulation
        auth('sanctum')->setUser($user1);

        $v2PostsCountUser1 = CommunityPost::count();
        $this->info("V2 Total Posts for EG User (Should be filtered to EG posts): {$v2PostsCountUser1}");

        // Cleanup
        $post1->delete();
        $post2->delete();
        $user1->delete();
        $user2->delete();

        $this->info("--- Tests Completed ---");
    }
}
