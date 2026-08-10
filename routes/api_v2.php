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

// V1 Endpoints automatically filtered by CountryScope in V2
Route::get('countries', [\App\Http\Controllers\Api\V1\CountryController::class, 'index']);
Route::get('services', [\App\Http\Controllers\Api\V1\ServiceController::class, 'index']);
Route::get('clubs', [\App\Http\Controllers\Api\V1\ClubController::class, 'index']);
Route::get('events', [\App\Http\Controllers\Api\V1\EventController::class, 'index']);
Route::get('posts', [\App\Http\Controllers\Api\V1\PostController::class, 'index']);
Route::get('users/{id}/posts', [\App\Http\Controllers\Api\V1\PostController::class, 'userPosts']);
Route::get('reels', [\App\Http\Controllers\Api\V1\PostController::class, 'reels']);
Route::get('community/posts', [\App\Http\Controllers\Api\V1\CommunityController::class, 'index']);

// Marketplace (Job Board) Public List
Route::get('market-requests', [MarketController::class, 'index']);

// --- Protected Routes (Require authentication) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // --- Profile Updates ---
    Route::post('profile/update', [ProfileController::class, 'update']);
    Route::post('market-requests', [MarketController::class, 'store']);
    Route::post('market-requests/{id}/apply', [MarketController::class, 'apply']);
    Route::get('market-requests/my-requests', [MarketController::class, 'myRequests']);
    
    // --- Personal Calendar ---
    Route::get('calendar', [CalendarController::class, 'index']);
    Route::post('calendar', [CalendarController::class, 'store']);
    Route::put('calendar/{id}', [CalendarController::class, 'update']);
    Route::delete('calendar/{id}', [CalendarController::class, 'destroy']);
    
});
