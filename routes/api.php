<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\QuestionController;
use App\Http\Controllers\Api\V1\CategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->middleware('set.api.locale')->group(function () {
    // Auth routes grouped under /api/v1/auth
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    // Public home endpoint for guests
    Route::get('home', [HomeController::class, 'index']);

    // Questions endpoints
    // Route::get('categories/{category}/questions', [QuestionController::class, 'index']);
    Route::get('questions', [QuestionController::class, 'index']);

    // Categories listing
    Route::get('categories', [CategoryController::class, 'index']);

    // --- Services Routes ---

    // Public Services
    Route::get('services', [App\Http\Controllers\Api\V1\ServiceController::class, 'index']);
    Route::get('services/{id}', [App\Http\Controllers\Api\V1\ServiceController::class, 'show']);

    // Public Profile
    Route::get('users/{id}/profile', [App\Http\Controllers\Api\V1\ProfileController::class, 'show']);

    // --- Community Routes (Public) ---
    Route::get('news', [App\Http\Controllers\Api\V1\NewsController::class, 'index']);
    Route::get('news/{id}', [App\Http\Controllers\Api\V1\NewsController::class, 'show']);
    Route::get('posts', [App\Http\Controllers\Api\V1\PostController::class, 'index']);
    Route::get('posts/{id}', [App\Http\Controllers\Api\V1\PostController::class, 'show']);

    // --- Events Routes (Public) ---
    Route::get('events', [App\Http\Controllers\Api\V1\EventController::class, 'index']);
    Route::get('events/{id}', [App\Http\Controllers\Api\V1\EventController::class, 'show']);

    // Protected Routes (Require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // Logout route under /api/v1/auth/logout
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // submit answers
        Route::post('questions/answers', [QuestionController::class, 'submit']);

        // --- Service User Actions ---
        Route::post('services/{id}/request', [App\Http\Controllers\Api\V1\ServiceRequestController::class, 'store']);
        Route::get('my-requests', [App\Http\Controllers\Api\V1\ServiceRequestController::class, 'index']);
        Route::post('services/{id}/rate', [App\Http\Controllers\Api\V1\ServiceReviewController::class, 'store']);

        // --- Provider Actions ---
        Route::get('provider/requests', [App\Http\Controllers\Api\V1\ProviderRequestController::class, 'index']);
        Route::post('provider/requests/{id}/status', [App\Http\Controllers\Api\V1\ProviderRequestController::class, 'updateStatus']);

        // --- Profile Actions ---
        Route::post('users/profile', [App\Http\Controllers\Api\V1\ProfileController::class, 'update']);
        Route::post('users/{id}/follow', [App\Http\Controllers\Api\V1\ProfileController::class, 'follow']);

        // --- Community Actions ---
        Route::post('posts', [App\Http\Controllers\Api\V1\PostController::class, 'store']);
        Route::delete('posts/{id}', [App\Http\Controllers\Api\V1\PostController::class, 'destroy']);
        Route::post('posts/{id}/like', [App\Http\Controllers\Api\V1\PostController::class, 'like']);
        Route::post('posts/{id}/comment', [App\Http\Controllers\Api\V1\PostController::class, 'comment']);

        // --- Event Booking ---
        Route::post('events/{id}/book', [App\Http\Controllers\Api\V1\EventBookingController::class, 'store']);
    });
});
