<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V2\FeedController;
use App\Http\Controllers\Api\V2\MarketController;
use App\Http\Controllers\Api\V2\CalendarController;
use App\Http\Controllers\Api\V1\ProfileController;

/*
|--------------------------------------------------------------------------
| API V2 Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for V2 of your application.
| These routes will be loaded in api.php and prefixed with 'v2'.
|
*/

// --- Public Routes (Guests allowed, filtered by Country-Id header) ---
Route::get('home', [\App\Http\Controllers\Api\V1\HomeController::class, 'index']);
Route::get('feed', [FeedController::class, 'index']);

// V1 Endpoints automatically filtered by CountryScope in V2 (or global)
Route::get('categories', [\App\Http\Controllers\Api\V1\CategoryController::class, 'index']);
Route::get('questions', [\App\Http\Controllers\Api\V1\QuestionController::class, 'index']);
Route::get('news', [\App\Http\Controllers\Api\V1\NewsController::class, 'index']);
Route::get('search', [\App\Http\Controllers\Api\V1\SearchController::class, 'index']);
Route::get('subscriptions/plans', [\App\Http\Controllers\Api\V1\SubscriptionController::class, 'plans']);
Route::get('settings', [\App\Http\Controllers\Api\V1\SettingController::class, 'index']);
Route::get('community/categories', [\App\Http\Controllers\Api\V1\CommunityController::class, 'getCategories']);

Route::get('countries', [\App\Http\Controllers\Api\V1\CountryController::class, 'index']);
Route::get('services', [\App\Http\Controllers\Api\V1\ServiceController::class, 'index']);
Route::get('clubs', [\App\Http\Controllers\Api\V1\ClubController::class, 'index']);
Route::get('events', [\App\Http\Controllers\Api\V1\EventController::class, 'index']);
Route::get('posts', [\App\Http\Controllers\Api\V1\PostController::class, 'index']);
Route::get('users/{id}/posts', [\App\Http\Controllers\Api\V1\PostController::class, 'userPosts']);
Route::get('users/{id}/profile', [\App\Http\Controllers\Api\V1\ProfileController::class, 'show']);
Route::get('users/{id}/followers', [\App\Http\Controllers\Api\V1\ProfileController::class, 'followers']);
Route::get('users/{id}/following', [\App\Http\Controllers\Api\V1\ProfileController::class, 'following']);
Route::get('users/{id}/ratings', [\App\Http\Controllers\Api\V1\ProfileController::class, 'ratings']);
Route::get('reels', [\App\Http\Controllers\Api\V1\PostController::class, 'reels']);
Route::get('community/posts', [\App\Http\Controllers\Api\V1\CommunityController::class, 'index']);
Route::get('clubs/{id}', [\App\Http\Controllers\Api\V1\ClubController::class, 'show']);
Route::get('clubs/{club_id}/teams', [\App\Http\Controllers\Api\V1\TeamController::class, 'index']);
Route::get('club/events', [\App\Http\Controllers\Api\V1\ClubEventController::class, 'index']);

// Marketplace (Job Board) Public List
Route::get('market-requests', [MarketController::class, 'index']);

// --- Protected Routes (Require authentication) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // --- Profile Updates ---
    Route::post('profile/update', [ProfileController::class, 'update']);
    Route::post('market-requests', [MarketController::class, 'store']);
    Route::post('market-requests/{id}/apply', [MarketController::class, 'apply']);
    Route::get('market-requests/my-requests', [MarketController::class, 'myRequests']);
    
    // --- V2 Calendar ---
    Route::get('calendar', [CalendarController::class, 'index']);
    Route::post('calendar', [CalendarController::class, 'store']);
    Route::put('calendar/{id}', [CalendarController::class, 'update']);
    Route::delete('calendar/{id}', [CalendarController::class, 'destroy']);

    // --- Added from V1 for mobile convenience ---
    Route::get('stories', [\App\Http\Controllers\Api\V1\StoryController::class, 'index']);
    Route::get('notifications', [\App\Http\Controllers\Api\V1\NotificationController::class, 'index']);
    Route::get('chat/conversations', [\App\Http\Controllers\Api\V1\ChatController::class, 'index']);
    Route::get('my-bookings', [\App\Http\Controllers\Api\V1\EventBookingController::class, 'index']);
    Route::get('my-requests', [\App\Http\Controllers\Api\V1\ServiceRequestController::class, 'index']);
    Route::get('club-requests', [\App\Http\Controllers\Api\V1\ClubRequestController::class, 'index']);
    Route::get('provider/requests', [\App\Http\Controllers\Api\V1\ProviderRequestController::class, 'index']);
    Route::get('users/verification/status', [\App\Http\Controllers\Api\V1\VerificationController::class, 'status']);
    
});
