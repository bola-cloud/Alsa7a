<?php

use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SportController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\LeagueController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\Dashboard;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']
], function () {
    Route::get('/terms', [\App\Http\Controllers\PageController::class, 'terms'])->name('terms');
    Route::get('/privacy', [\App\Http\Controllers\PageController::class, 'privacy'])->name('privacy');
});

Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => [
        'auth:sanctum',
        'localeSessionRedirect', 
        'localizationRedirect', 
        'localeViewPath',
        config('jetstream.auth_session'),
        'verified',
    ]
], function () {
    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [Dashboard::class, 'index'])->name('dashboard');

        Route::get('categories/{category}/verification', [CategoryController::class, 'verification'])->name('categories.verification');
        Route::put('categories/{category}/verification', [CategoryController::class, 'updateVerification'])->name('categories.update_verification');
        Route::resource('categories', CategoryController::class);
        Route::resource('parent_categories', \App\Http\Controllers\Admin\ParentCategoryController::class);
        Route::post('questions/reorder', [\App\Http\Controllers\Admin\QuestionController::class, 'reorder'])->name('questions.reorder');
        Route::get('questions/{question}/answers', [\App\Http\Controllers\Admin\QuestionController::class, 'answers'])->name('questions.answers');
        Route::resource('questions', \App\Http\Controllers\Admin\QuestionController::class);
        Route::resource('sports', SportController::class);
        Route::resource('sliders', SliderController::class);
        Route::resource('leagues', LeagueController::class);
        Route::post('events/{event}/approve', [EventController::class, 'approve'])->name('events.approve');
        Route::resource('events', EventController::class);

        // Club Management
        Route::post('clubs/bulk', [\App\Http\Controllers\Admin\ClubController::class, 'bulk'])->name('clubs.bulk');
        Route::resource('clubs', \App\Http\Controllers\Admin\ClubController::class);
        Route::resource('clubs.teams', \App\Http\Controllers\Admin\TeamController::class);
        Route::post('clubs/{club}/teams/{team}/add-member', [\App\Http\Controllers\Admin\TeamController::class, 'addMember'])->name('clubs.teams.add_member');
        Route::delete('clubs/{club}/teams/{team}/remove-member/{user}', [\App\Http\Controllers\Admin\TeamController::class, 'removeMember'])->name('clubs.teams.remove_member');

        // User Verification & Management
        Route::post('users/{user}/approve', [\App\Http\Controllers\Admin\UserController::class, 'approve'])->name('users.approve');
        Route::post('users/{user}/verify', [\App\Http\Controllers\Admin\UserController::class, 'verifyDocuments'])->name('users.verify');
        Route::post('users/{user}/verify-phone', [\App\Http\Controllers\Admin\UserController::class, 'verifyPhone'])->name('users.verify_phone');
        Route::post('users/{user}/toggle-block', [\App\Http\Controllers\Admin\UserController::class, 'toggleBlock'])->name('users.toggle_block');
        Route::post('users/{user}/activate-subscription', [\App\Http\Controllers\Admin\UserController::class, 'activateSubscription'])->name('users.activate_subscription');
        Route::post('users/{user}/cancel-subscription', [\App\Http\Controllers\Admin\UserController::class, 'cancelSubscription'])->name('users.cancel_subscription');
        Route::get('users/search', [\App\Http\Controllers\Admin\UserController::class, 'searchUsers'])->name('users.search'); // Search API
        Route::get('verification/manual', [\App\Http\Controllers\Admin\UserController::class, 'manualVerificationIndex'])->name('verification.manual');
        Route::get('clubs/{club}/teams-json', [\App\Http\Controllers\Admin\UserController::class, 'getClubTeams'])->name('clubs.teams_json');
        Route::post('users/bulk', [\App\Http\Controllers\Admin\UserController::class, 'bulk'])->name('users.bulk');
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::resource('otps', \App\Http\Controllers\Admin\OtpController::class)->only(['index']);

        // Community
        Route::resource('news', \App\Http\Controllers\Admin\NewsController::class);
        Route::get('posts', [\App\Http\Controllers\Admin\PostController::class, 'index'])->name('posts.index');
        Route::delete('posts/{post}', [\App\Http\Controllers\Admin\PostController::class, 'destroy'])->name('posts.destroy');
        Route::post('posts/{post}/toggle', [\App\Http\Controllers\Admin\PostController::class, 'toggle'])->name('posts.toggle');
        Route::get('settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

        // Reports
        Route::get('reports/financial', [App\Http\Controllers\Admin\ReportController::class, 'financial'])->name('reports.financial');
        Route::get('reports/analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('reports.analytics');
        Route::get('reports/user-activities', [\App\Http\Controllers\Admin\UserActivityController::class, 'index'])->name('reports.user_activities');

        // Community Posts (Blogs)
        Route::get('community_posts', [\App\Http\Controllers\Admin\CommunityPostController::class, 'index'])->name('community_posts.index');
        Route::delete('community_posts/{id}', [\App\Http\Controllers\Admin\CommunityPostController::class, 'destroy'])->name('community_posts.destroy');
        Route::post('community_posts/{id}/toggle', [\App\Http\Controllers\Admin\CommunityPostController::class, 'toggle'])->name('community_posts.toggle');
        Route::resource('community_categories', \App\Http\Controllers\Admin\CommunityCategoryController::class);

        // Stories
        Route::get('stories', [\App\Http\Controllers\Admin\StoryController::class, 'index'])->name('stories.index');
        Route::delete('stories/{id}', [\App\Http\Controllers\Admin\StoryController::class, 'destroy'])->name('stories.destroy');

        // Services & Tickets
        Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class)->only(['index', 'show', 'destroy']);
        Route::post('services/{service}/toggle', [\App\Http\Controllers\Admin\ServiceController::class, 'toggle'])->name('services.toggle');
        Route::post('services/{service}/toggle-featured', [\App\Http\Controllers\Admin\ServiceController::class, 'toggleFeatured'])->name('services.toggle_featured');
        Route::resource('service_requests', \App\Http\Controllers\Admin\ServiceRequestController::class)->only(['index', 'show', 'update']); // Added update
        Route::get('/global-search', [\App\Http\Controllers\Admin\GlobalSearchController::class, 'search'])->name('global_search');
        Route::resource('tickets', \App\Http\Controllers\Admin\TicketController::class)->only(['index', 'show', 'update']);

        // Notifications
        Route::get('notifications/fetch', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.fetch');
        Route::post('notifications/mark-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.mark_read');
        Route::post('notifications/mark-read/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'markSingleAsRead'])->name('notifications.mark_single_read');
        Route::get('notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'create'])->name('notifications.create');
        Route::post('notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'store'])->name('notifications.store');
    });
});

// Payment Callbacks (Web view)
// Payment Callbacks
Route::get('/payment/success', [\App\Http\Controllers\Api\V1\PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel', [\App\Http\Controllers\Api\V1\PaymentController::class, 'cancel'])->name('payment.cancel');

// Temporary Reverb Test Route
Route::get('/chat-test', function () {
    return view('chat-test');
});

// Deep Link Fallback (App Links / Universal Links)
// Note: /app is reserved by Reverb WebSocket (Nginx proxies it to port 6001)
// Use /share prefix instead for deep links
Route::prefix('share')->group(function () {
    Route::get('{any?}', [\App\Http\Controllers\ShareController::class, 'handle'])->where('any', '.*');
});