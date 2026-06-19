<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Vercel's internal proxy talks to PHP over plain HTTP even though
        // the browser connects via HTTPS, so Laravel's asset()/url() helpers
        // would otherwise generate http:// links and get blocked as mixed
        // content. Force https in production so all generated URLs are correct.
        if ($this->app->environment('production') || str_contains((string) env('APP_URL'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}