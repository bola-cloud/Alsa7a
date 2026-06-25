<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

// Check posts with images
$post = App\Models\Post::with(['images','mentions'])
    ->whereHas('images')
    ->where('processing_status','completed')
    ->where('is_hidden', false)
    ->latest()
    ->first();

if ($post) {
    echo "POST WITH IMAGES:\n";
    echo "Post ID: " . $post->id . "\n";
    echo "Images count: " . $post->images->count() . "\n";
    echo "Mentions count: " . $post->mentions->count() . "\n";
    echo "Images: " . json_encode($post->images->pluck('url')) . "\n";
} else {
    echo "NO POSTS WITH IMAGES IN DB\n";
}

// Check posts with mentions
$postMention = App\Models\Post::with(['images','mentions'])
    ->whereHas('mentions')
    ->where('processing_status','completed')
    ->where('is_hidden', false)
    ->latest()
    ->first();

if ($postMention) {
    echo "\nPOST WITH MENTIONS:\n";
    echo "Post ID: " . $postMention->id . "\n";
    echo "Mentions count: " . $postMention->mentions->count() . "\n";
    echo "Mentions: " . json_encode($postMention->mentions->pluck('name')) . "\n";
} else {
    echo "\nNO POSTS WITH MENTIONS IN DB\n";
}

// Check parent_code in user model
$user = App\Models\User::first();
echo "\nUSER FIELDS:\n";
echo "parent_code field exists: " . (array_key_exists('parent_code', $user->toArray()) ? 'YES' : 'NO') . "\n";
echo "parent_code value: " . ($user->parent_code ?? 'NULL') . "\n";
