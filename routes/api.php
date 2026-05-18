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
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('send-otp', [AuthController::class, 'sendOtp']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
        Route::get('clubs-available', [AuthController::class, 'clubsAvailable']); // New
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
    Route::get('services-activity', [App\Http\Controllers\Api\V1\ServiceRequestController::class, 'activity']);

    // Public Profile
    // Public Profile
    Route::get('users/{id}/profile', [App\Http\Controllers\Api\V1\ProfileController::class, 'show']);
    Route::get('users/{id}/posts', [App\Http\Controllers\Api\V1\PostController::class, 'userPosts']);

    // --- Community Routes (Public) ---
    Route::get('news', [App\Http\Controllers\Api\V1\NewsController::class, 'index']);
    Route::get('news/{id}', [App\Http\Controllers\Api\V1\NewsController::class, 'show']);
    Route::get('posts', [App\Http\Controllers\Api\V1\PostController::class, 'index']); // Public Feed of Profile Posts
    Route::get('reels', [App\Http\Controllers\Api\V1\PostController::class, 'reels']); // Public Reels Feed (Videos only)
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

    // --- Feed Routes (Public) ---
    Route::get('feed', [App\Http\Controllers\Api\V1\FeedController::class, 'index']);

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
        Route::post('chat/conversations', [App\Http\Controllers\Api\V1\ChatController::class, 'create']); // Start/Get Chat
        Route::get('chat/conversations/{id}', [App\Http\Controllers\Api\V1\ChatController::class, 'show']);
        Route::post('chat/conversations/{id}/messages', [App\Http\Controllers\Api\V1\ChatController::class, 'store']);

        // --- Club Routes ---
        Route::get('clubs', [App\Http\Controllers\Api\V1\ClubController::class, 'index']);
        Route::get('clubs/{id}', [App\Http\Controllers\Api\V1\ClubController::class, 'show']);
        Route::post('clubs/{id}/leagues', [App\Http\Controllers\Api\V1\ClubController::class, 'updateLeagues']);
        
        // Club Events
        Route::get('club/events', [App\Http\Controllers\Api\V1\ClubEventController::class, 'index']);
        Route::post('club/events', [App\Http\Controllers\Api\V1\ClubEventController::class, 'store']);

        // Team Management
        Route::get('clubs/{club_id}/teams', [App\Http\Controllers\Api\V1\TeamController::class, 'index']);
        Route::post('clubs/{club_id}/teams', [App\Http\Controllers\Api\V1\TeamController::class, 'store']);
        Route::get('teams/{id}', [App\Http\Controllers\Api\V1\TeamController::class, 'show']);
        Route::post('teams/{id}', [App\Http\Controllers\Api\V1\TeamController::class, 'update']); // Use POST for update to support images
        Route::delete('teams/{id}', [App\Http\Controllers\Api\V1\TeamController::class, 'destroy']);
        Route::post('teams/{id}/add-member', [App\Http\Controllers\Api\V1\TeamController::class, 'addMember']);
        Route::post('teams/{id}/remove-member', [App\Http\Controllers\Api\V1\TeamController::class, 'removeMember']);

        // --- Club Requests (Join/Invite) ---
        Route::get('club-requests', [App\Http\Controllers\Api\V1\ClubRequestController::class, 'index']);
        Route::post('club-requests', [App\Http\Controllers\Api\V1\ClubRequestController::class, 'store']); // Create request
        Route::post('club-requests/{id}/respond', [App\Http\Controllers\Api\V1\ClubRequestController::class, 'respond']); // Accept/Reject
        Route::delete('club-requests/{id}', [App\Http\Controllers\Api\V1\ClubRequestController::class, 'destroy']); // Cancel/Delete

        // --- Provider Actions ---
        Route::get('provider/requests', [App\Http\Controllers\Api\V1\ProviderRequestController::class, 'index']);
        Route::post('provider/requests/{id}/status', [App\Http\Controllers\Api\V1\ProviderRequestController::class, 'updateStatus']);

        // --- Profile Actions ---
        Route::post('users/profile', [App\Http\Controllers\Api\V1\ProfileController::class, 'update']);
        Route::delete('users/account', [App\Http\Controllers\Api\V1\ProfileController::class, 'destroyAccount']);
        Route::post('users/{id}/follow', [App\Http\Controllers\Api\V1\ProfileController::class, 'follow']);
        Route::get('users/{id}/followers', [App\Http\Controllers\Api\V1\ProfileController::class, 'followers']);
        Route::get('users/{id}/following', [App\Http\Controllers\Api\V1\ProfileController::class, 'following']);
        Route::post('users/{id}/rate', [App\Http\Controllers\Api\V1\ProfileController::class, 'rate']);
        Route::get('users/{id}/ratings', [App\Http\Controllers\Api\V1\ProfileController::class, 'ratings']);

        // --- Feed Actions ---
        Route::post('feed/seen', [App\Http\Controllers\Api\V1\FeedController::class, 'markAsSeen']);

        // --- Post/Community Actions ---
        Route::post('posts', [App\Http\Controllers\Api\V1\PostController::class, 'store']); // Profile Posts (Instagram)
        Route::post('posts/{id}', [App\Http\Controllers\Api\V1\PostController::class, 'update']);
        Route::delete('posts/{id}', [App\Http\Controllers\Api\V1\PostController::class, 'destroy']);
        Route::post('posts/{id}/like', [App\Http\Controllers\Api\V1\PostController::class, 'like']);
        Route::get('posts/{id}/likes', [App\Http\Controllers\Api\V1\PostController::class, 'likes']); // Get list of likers
        Route::post('posts/{id}/comment', [App\Http\Controllers\Api\V1\CommentController::class, 'store']); // Support singular for backward compatibility

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
        Route::get('my-bookings', [App\Http\Controllers\Api\V1\EventBookingController::class, 'index']);

        // --- Subscriptions ---
        Route::get('subscriptions/plans', [App\Http\Controllers\Api\V1\SubscriptionController::class, 'plans'])->withoutMiddleware('auth:sanctum');
        Route::post('subscriptions/checkout', [App\Http\Controllers\Api\V1\SubscriptionController::class, 'checkout']);
        Route::get('subscriptions/status', [App\Http\Controllers\Api\V1\SubscriptionController::class, 'status']);

        // --- Notifications ---
        Route::get('notifications', [App\Http\Controllers\Api\V1\NotificationController::class, 'index']);
        Route::post('notifications/{id}/read', [App\Http\Controllers\Api\V1\NotificationController::class, 'markAsRead']);
        Route::post('notifications/read-all', [App\Http\Controllers\Api\V1\NotificationController::class, 'markAllAsRead']);
        Route::delete('notifications/{id}', [App\Http\Controllers\Api\V1\NotificationController::class, 'destroy']);

        // --- Verification ---
        Route::post('users/verification/upload', [App\Http\Controllers\Api\V1\VerificationController::class, 'upload']);
        Route::get('users/verification/status', [App\Http\Controllers\Api\V1\VerificationController::class, 'status']);

        // --- Reverb Test ---
        Route::get('reverb-test', function () {
            try {
                $fp = fsockopen("127.0.0.1", 6001, $errno, $errstr, 3);
                if (!$fp) {
                    return response()->json(['status' => false, 'message' => "Reverb Down: $errstr ($errno)"], 500);
                }
                fclose($fp);
                return response()->json(['status' => true, 'message' => 'Reverb is RUNNING and listening on port 6001']);
            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => 'Reverb Error: ' . $e->getMessage()], 500);
            }
        });

        // --- Notification Test ---
        Route::get('test-create-notification', function (Illuminate\Http\Request $request) {
            $request->user()->notify(new App\Notifications\ServiceRequestNotification([
                'title' => 'Test Notification',
                'body' => 'This is a test notification generated at ' . now(),
                'type' => 'test',
                'request_id' => 1,
                'service_id' => 1
            ]));
            return response()->json(['status' => true, 'message' => 'Test notification created']);
        });
    });
});
