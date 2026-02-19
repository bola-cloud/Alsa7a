<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\UserRating;

try {
    // 1. Test Ratings
    $u1 = User::find(1);
    $u2 = User::find(2);
    if ($u1 && $u2) {
        UserRating::updateOrCreate(
            ['reviewer_id' => $u1->id, 'rated_id' => $u2->id],
            ['rating' => 5, 'comment' => 'Test Rating']
        );
        echo "Rating Created for User 2. Average: " . $u2->ratingsReceived()->avg('rating') . "\n";
    }

    // 2. Test show_answers
    $u1->show_answers = false;
    $u1->save();
    echo "User 1 show_answers set to: " . ($u1->show_answers ? "true" : "false") . "\n";

    // 3. Test category_id
    $u1->category_id = 1; // Assuming 1 exists
    $u1->save();
    echo "User 1 category_id set to: " . $u1->category_id . "\n";

    echo "Verification Script Success\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
