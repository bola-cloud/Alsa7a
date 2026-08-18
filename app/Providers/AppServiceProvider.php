<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Models\Country;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('Helpers/settings.php'))) {
            require_once app_path('Helpers/settings.php');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        if (request()->server('HTTP_X_FORWARDED_PROTO') == 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Force APP_URL if we are on the production domain (fixes asset() returning localhost)
        if (request()->getHost() === 'saha.wasl-x.com' || config('app.env') === 'production') {
            config(['app.url' => 'https://saha.wasl-x.com']);
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \App\Models\User::observe(\App\Observers\UserObserver::class);

        // Share active countries with the admin layout for the global country filter
        View::composer('layouts.admin', function ($view) {
            $view->with('adminActiveCountries', Country::where('is_active', true)->ordered()->get());
        });
    }
}
