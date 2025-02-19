<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

use App\Http\ViewComposers\PickupRequestComposer;
use App\Http\ViewComposers\ShiperAdviseComposer;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Using class based composers...
        View::composer(['layouts.partials.left-side-bar'], PickupRequestComposer::class);
        View::composer(['layouts.partials.left-side-bar'], ShiperAdviseComposer::class);
        //View::composer(['layouts.partials.left-side-bar'], PickupRequestComposer::class);

        // Using closure based composers...
        /*View::composer('dashboard', function ($view) {
            //
        });*/
    }
}
