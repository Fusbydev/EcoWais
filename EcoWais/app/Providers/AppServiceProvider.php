<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Driver;

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
    View::composer('layouts.nav_collector', function ($view) {
        $userId = session('user_id'); // get current logged-in user_id
        $driver = Driver::with('truck')->where('user_id', $userId)->first();
        $view->with('driver', $driver);
    });
}

}
