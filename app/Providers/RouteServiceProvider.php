<?php

namespace App\Providers;

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\DriverMiddleware;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/admin/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            // API Routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Web Routes (Auth and Public Routes)
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Admin API Routes
            Route::middleware(['api', 'auth:sanctum'])
                ->prefix('api/admin')
                ->name('admin.api.')
                ->group(base_path('routes/admin.php'));

            // Admin Web Routes
            Route::middleware(['web', 'auth', AdminMiddleware::class])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/web_admin.php'));

            // Driver Routes
            Route::middleware(['web', 'auth', DriverMiddleware::class])
                ->prefix('driver')
                ->name('driver.')
                ->group(base_path('routes/driver.php'));

            // Debug Routes (only in local environment)
            if (app()->environment('local')) {
                Route::middleware('web')
                    ->group(base_path('routes/debug.php'));
            }
        });
    }
}
