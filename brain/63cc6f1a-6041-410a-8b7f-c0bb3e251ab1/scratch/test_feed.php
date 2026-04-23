<?php

use App\Models\User;
use App\Models\Post;
use App\Models\Like;
use App\Services\FeedService;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Create/Get Test Users
$user = User::first(); // The logged in user
$author = User::where('id', '!=', $user->id)->first(); // The post author

if (!$user || !$author) {
    die("Need at least 2 users in DB to test.\n");
}

// 2. Ensure a follow relationship exists
$user->following()->syncWithoutDetaching([$author->id]);

// 3. Create a post for the author
$post = Post::create([
    'user_id' => $author->id,
    'content' => 'Test Post ' . time(),
    'is_hidden' => false,
    'type' => 'text'
]);

// 4. Ensure the user liked the post
$post->likes()->create(['user_id' => $user->id]);

// 5. Run FeedService
$feedService = new FeedService();
$feed = $feedService->getFeed($user, 10);

// 6. Verify first item (should be the test post)
$postInFeed = collect($feed->items())->firstWhere('id', $post->id);

if (!$postInFeed) {
    echo "Post not found in feed. (Maybe it was already seen?)\n";
    // Check all items
    echo "Feed items count: " . count($feed->items()) . "\n";
    $postInFeed = collect($feed->items())->first();
}

if ($postInFeed) {
    echo "Post ID: " . $postInFeed->id . "\n";
    echo "is_liked: " . ($postInFeed->is_liked ? 'TRUE' : 'FALSE') . "\n";
    
    if ($postInFeed->user) {
        echo "Author ID: " . $postInFeed->user->id . "\n";
        echo "Author is_following: " . ($postInFeed->user->is_following ? 'TRUE' : 'FALSE') . "\n";
    } else {
        echo "Author not loaded!\n";
    }

    // Check JSON serialization
    $json = json_encode($postInFeed);
    echo "JSON contains is_liked: " . (str_contains($json, '"is_liked":true') ? 'YES' : 'NO') . "\n";
    echo "JSON contains is_following: " . (str_contains($json, '"is_following":true') ? 'YES' : 'NO') . "\n";
} else {
    echo "No posts in feed.\n";
}

// Cleanup (optional)
// $post->delete();
