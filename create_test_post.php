<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(26);
$post = App\Models\Post::create([
    'user_id' => $user->id, 
    'content' => 'Test multiple', 
    'type' => 'image', 
    'processing_status' => 'completed', 
    'is_hidden' => false
]); 
$post->images()->create(['image_path' => 'test1.jpg']); 
$post->images()->create(['image_path' => 'test2.jpg']); 
$post->mentions()->sync([152, 149]); 

echo 'Created Post: '.$post->id;
