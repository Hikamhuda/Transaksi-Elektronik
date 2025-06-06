<?php

namespace App\Providers;

use App\Providers\EncryptedUserProvider;
use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Auth::provider('encrypted-eloquent', function ($app, array $config) {
            return new EncryptedUserProvider($app['hash'], $config['model']);
        });
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
