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
            $enterenueDBProductsCount = Enterenue::where('pushed', 1)->count();
            $view->with('enterenueDBProductsCount', $enterenueDBProductsCount);
            $enterenueShopifyProductsCount = Enterenue::all()->count();
            $view->with('enterenueShopifyProductsCount', $enterenueShopifyProductsCount);
        });
    }
}
