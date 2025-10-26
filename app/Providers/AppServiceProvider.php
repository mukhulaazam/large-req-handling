<?php

namespace App\Providers;

use App\Services\RequestTracker;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind as scoped, not singleton
        // This ensures each HTTP request gets a fresh instance
        // The singleton pattern would cause state to leak between concurrent users
        $this->app->scoped(RequestTracker::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
