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
    Route::get('/', [Dashboard::class, 'index'])->name('dashboard');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::resource('sports', SportController::class);
        Route::resource('sliders', SliderController::class);
        Route::resource('leagues', \App\Http\Controllers\Admin\LeagueController::class);
        Route::resource('events', \App\Http\Controllers\Admin\EventController::class);

        // Community
        Route::resource('news', \App\Http\Controllers\Admin\NewsController::class);
        Route::get('posts', [\App\Http\Controllers\Admin\PostController::class, 'index'])->name('posts.index');
        Route::delete('posts/{post}', [\App\Http\Controllers\Admin\PostController::class, 'destroy'])->name('posts.destroy');
        Route::post('posts/{post}/toggle', [\App\Http\Controllers\Admin\PostController::class, 'toggle'])->name('posts.toggle');
        Route::get('settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

        // Services & Tickets
        Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class)->only(['index', 'show', 'destroy']);
        Route::resource('service_requests', \App\Http\Controllers\Admin\ServiceRequestController::class)->only(['index', 'show']);
        Route::resource('tickets', \App\Http\Controllers\Admin\TicketController::class)->only(['index', 'show', 'update']);
    });
});