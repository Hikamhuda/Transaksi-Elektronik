<?php

namespace App\Providers;

use Filament\Facades\Filament;
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
    public function boot()
{
    Filament::serving(function () {
        Filament::registerNavigationGroups([
            'Inventory Management',
            'Financial Reports',
            'Settings',
        ]);
    });
}
}
