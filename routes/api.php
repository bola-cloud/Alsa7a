<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\QuestionController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\SearchController;

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

    // General Settings
    Route::get('settings', [App\Http\Controllers\Api\V1\SettingController::class, 'index']);

    // --- Services Routes ---

    // Public Services
    Route::get('services', [App\Http\Controllers\Api\V1\ServiceController::class, 'index']);
    Route::get('services/{id}', [App\Http\Controllers\Api\V1\ServiceController::class, 'show']);

    // Public Profile
    // Public Profile
    Route::get('users/{id}/profile', [App\Http\Controllers\Api\V1\ProfileController::class, 'show']);
    Route::get('users/{id}/posts', [App\Http\Controllers\Api\V1\PostController::class, 'userPosts']);

    // --- Community Routes (Public) ---
    Route::get('news', [App\Http\Controllers\Api\V1\NewsController::class, 'index']);
    Route::get('news/{id}', [App\Http\Controllers\Api\V1\NewsController::class, 'show']);
    Route::get('posts', [App\Http\Controllers\Api\V1\PostController::class, 'index']); // Public Feed of Profile Posts
    Route::get('posts/{id}', [App\Http\Controllers\Api\V1\PostController::class, 'show']);

    // Community Blogs (Categorized)
    Route::get('community/categories', [App\Http\Controllers\Api\V1\CommunityController::class, 'getCategories']);
    Route::get('community/posts', [App\Http\Controllers\Api\V1\CommunityController::class, 'index']);
    Route::get('community/posts/{id}', [App\Http\Controllers\Api\V1\CommunityController::class, 'show']);

    // --- Search ---
    Route::get('search', [SearchController::class, 'index']);

    // --- Events Routes (Public) ---
    Route::get('events', [App\Http\Controllers\Api\V1\EventController::class, 'index']);
    Route::get('events/{id}', [App\Http\Controllers\Api\V1\EventController::class, 'show']);

    // Protected Routes (Require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::get('my-profile', [App\Http\Controllers\Api\V1\ProfileController::class, 'me']);

        // Logout route under /api/v1/auth/logout
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('users/onesignal-subscription', [AuthController::class, 'updateSubscription']);

        // submit answers
        Route::post('questions/answers', [QuestionController::class, 'submit']);

        // --- Service User Actions ---
        Route::post('services', [App\Http\Controllers\Api\V1\ServiceController::class, 'store']); // Create Service
        Route::post('services/{id}', [App\Http\Controllers\Api\V1\ServiceController::class, 'update']); // Update Service (POST for file support)
        Route::delete('services/{id}', [App\Http\Controllers\Api\V1\ServiceController::class, 'destroy']); // Delete Service
        Route::post('services/{id}/request', [App\Http\Controllers\Api\V1\ServiceRequestController::class, 'store']);
        // Route::post('requests/{id}/pay', [App\Http\Controllers\Api\V1\ServiceRequestController::class, 'pay']); // Payment Old
        Route::post('requests/pay', [App\Http\Controllers\Api\V1\PaymentController::class, 'pay']); // New Thawani Payload
        Route::get('payment/status', [App\Http\Controllers\Api\V1\PaymentController::class, 'checkStatus']);
        Route::post('payment/webhook', [App\Http\Controllers\Api\V1\PaymentController::class, 'webhook']);
        Route::get('my-requests', [App\Http\Controllers\Api\V1\ServiceRequestController::class, 'index']);
        Route::post('requests/{id}/cancel', [App\Http\Controllers\Api\V1\ServiceRequestController::class, 'cancel']); // Cancel Request
        Route::post('services/{id}/rate', [App\Http\Controllers\Api\V1\ServiceReviewController::class, 'store']);

        // --- Chat Routes ---
        Route::get('chat/conversations', [App\Http\Controllers\Api\V1\ChatController::class, 'index']);
        Route::get('chat/conversations/{id}', [App\Http\Controllers\Api\V1\ChatController::class, 'show']);
        Route::post('chat/conversations/{id}/messages', [App\Http\Controllers\Api\V1\ChatController::class, 'store']);

        // --- Club Routes ---
        Route::get('clubs', [App\Http\Controllers\Api\V1\ClubController::class, 'index']);
        Route::get('clubs/{id}', [App\Http\Controllers\Api\V1\ClubController::class, 'show']);
        Route::post('clubs/{id}/leagues', [App\Http\Controllers\Api\V1\ClubController::class, 'updateLeagues']);

        // --- Provider Actions ---
        Route::get('provider/requests', [App\Http\Controllers\Api\V1\ProviderRequestController::class, 'index']);
        Route::post('provider/requests/{id}/status', [App\Http\Controllers\Api\V1\ProviderRequestController::class, 'updateStatus']);

        // --- Profile Actions ---
        Route::post('users/profile', [App\Http\Controllers\Api\V1\ProfileController::class, 'update']);
        Route::post('users/{id}/follow', [App\Http\Controllers\Api\V1\ProfileController::class, 'follow']);
        Route::get('users/{id}/followers', [App\Http\Controllers\Api\V1\ProfileController::class, 'followers']);
        Route::get('users/{id}/following', [App\Http\Controllers\Api\V1\ProfileController::class, 'following']);

        // --- Post/Community Actions ---
        Route::post('posts', [App\Http\Controllers\Api\V1\PostController::class, 'store']); // Profile Posts (Instagram)
        Route::post('posts/{id}', [App\Http\Controllers\Api\V1\PostController::class, 'update']);
        Route::delete('posts/{id}', [App\Http\Controllers\Api\V1\PostController::class, 'destroy']);
        Route::post('posts/{id}/like', [App\Http\Controllers\Api\V1\PostController::class, 'like']);
        Route::get('posts/{id}/likes', [App\Http\Controllers\Api\V1\PostController::class, 'likes']); // Get list of likers
        // Route::post('posts/{id}/comment', [App\Http\Controllers\Api\V1\PostController::class, 'comment']); // Deprecated singular

        // Comments System
        Route::get('posts/{id}/comments', [App\Http\Controllers\Api\V1\CommentController::class, 'index']);
        Route::post('posts/{id}/comments', [App\Http\Controllers\Api\V1\CommentController::class, 'store']);
        Route::post('comments/{id}', [App\Http\Controllers\Api\V1\CommentController::class, 'update']);
        Route::delete('comments/{id}', [App\Http\Controllers\Api\V1\CommentController::class, 'destroy']);

        // --- Community Blog Actions ---
        Route::post('community/posts', [App\Http\Controllers\Api\V1\CommunityController::class, 'store']);
        Route::post('community/posts/{id}', [App\Http\Controllers\Api\V1\CommunityController::class, 'update']);
        Route::delete('community/posts/{id}', [App\Http\Controllers\Api\V1\CommunityController::class, 'destroy']);
        Route::post('community/posts/{id}/like', [App\Http\Controllers\Api\V1\CommunityController::class, 'like']);
        Route::get('community/posts/{id}/comments', [App\Http\Controllers\Api\V1\CommunityController::class, 'getComments']);
        Route::post('community/posts/{id}/comments', [App\Http\Controllers\Api\V1\CommunityController::class, 'comment']);

        // --- Event Booking ---
        Route::post('events/{id}/book', [App\Http\Controllers\Api\V1\EventBookingController::class, 'store']);
        Route::get('my-bookings', [App\Http\Controllers\Api\V1\EventBookingController::class, 'index']); // New Endpoint

        // --- Verification ---
        Route::post('users/verification/upload', [App\Http\Controllers\Api\V1\VerificationController::class, 'upload']);
        Route::get('users/verification/status', [App\Http\Controllers\Api\V1\VerificationController::class, 'status']);
    });
});
