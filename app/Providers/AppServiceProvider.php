<?php

namespace App\Providers;

use App\Models\Enterenue;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer('*', function ($view) {
            $enterenueDBProductsCount = Enterenue::all()->count();
            $view->with('enterenueDBProductsCount', $enterenueDBProductsCount);
        });
    }
}
