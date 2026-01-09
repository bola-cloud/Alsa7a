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
    'middleware' => [
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
    ]
], function () {
    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [Dashboard::class, 'index'])->name('dashboard');

        Route::resource('categories', CategoryController::class);
        Route::resource('sports', SportController::class);
        Route::resource('sliders', SliderController::class);
        Route::resource('leagues', LeagueController::class);
        Route::resource('events', EventController::class);

        // Club Management
        Route::resource('clubs', \App\Http\Controllers\Admin\ClubController::class);

        // User Verification & Management
        Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
        Route::post('users/{user}/approve', [\App\Http\Controllers\Admin\UserController::class, 'approve'])->name('users.approve');
        Route::post('users/{user}/verify', [\App\Http\Controllers\Admin\UserController::class, 'verifyDocuments'])->name('users.verify');

        // Community
        Route::resource('news', \App\Http\Controllers\Admin\NewsController::class);
        Route::get('posts', [\App\Http\Controllers\Admin\PostController::class, 'index'])->name('posts.index');
        Route::delete('posts/{post}', [\App\Http\Controllers\Admin\PostController::class, 'destroy'])->name('posts.destroy');
        Route::post('posts/{post}/toggle', [\App\Http\Controllers\Admin\PostController::class, 'toggle'])->name('posts.toggle');
        Route::get('settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

        // Reports
        Route::get('reports/financial', [App\Http\Controllers\Admin\ReportController::class, 'financial'])->name('reports.financial');

        // Services & Tickets
        Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class)->only(['index', 'show', 'destroy']);
        Route::post('services/{service}/toggle', [\App\Http\Controllers\Admin\ServiceController::class, 'toggle'])->name('services.toggle');
        Route::resource('service_requests', \App\Http\Controllers\Admin\ServiceRequestController::class)->only(['index', 'show', 'update']); // Added update
        Route::get('/global-search', [\App\Http\Controllers\Admin\GlobalSearchController::class, 'search'])->name('global_search');
        Route::resource('tickets', \App\Http\Controllers\Admin\TicketController::class)->only(['index', 'show', 'update']);
    });
});