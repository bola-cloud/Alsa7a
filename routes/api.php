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


    // Protected Routes (Require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // Logout route under /api/v1/auth/logout
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // submit answers
        Route::post('questions/answers', [QuestionController::class, 'submit']);
    });
});
