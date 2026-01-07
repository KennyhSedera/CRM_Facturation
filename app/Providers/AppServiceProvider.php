<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ne rien mettre ici - laisser Nutgram se configurer automatiquement
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
