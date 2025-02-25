<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class ComponentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Blade::component('layouts.app', 'app-layout');
        Blade::component('components.navigation', 'navigation');
        Blade::component('components.nav-link', 'nav-link');
        Blade::component('components.responsive-nav-link', 'responsive-nav-link');
        Blade::component('components.dropdown', 'dropdown');
        Blade::component('components.dropdown-link', 'dropdown-link');
        Blade::component('components.loading-spinner', 'loading-spinner');
    }
}
