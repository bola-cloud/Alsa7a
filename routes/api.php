<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| The definitions themselves live in routes/api_v1.php (every V1 endpoint)
| and routes/api_v2.php (the V2-only endpoints plus the ones V2 makes public
| and country-filtered).
|
*/

// V1 Routes — unchanged contract, no country filtering.
Route::prefix('v1')->middleware('set.api.locale')->group(base_path('routes/api_v1.php'));

// V2 Routes — a complete superset of V1, so the mobile app can send every
// request to /api/v2 without getting a 404 on an endpoint V2 never redefined.
// Order matters: the V1 file is registered first and api_v2.php after it, so
// the routes V2 changes (public access + CountryScope filtering) win.
Route::prefix('v2')->middleware('set.api.locale')->group(base_path('routes/api_v1.php'));
Route::prefix('v2')->middleware('set.api.locale')->group(base_path('routes/api_v2.php'));
