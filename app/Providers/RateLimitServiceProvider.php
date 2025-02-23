<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class RateLimitServiceProvider extends ServiceProvider
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
        // Health Check Rate Limiter
        RateLimiter::for('health-check', function (Request $request) {
            // Allow more requests from admin users
            if ($request->user()?->isAdmin()) {
                return Limit::perMinute(60);
            }

            // Allow more requests from local environment
            if (app()->environment('local')) {
                return Limit::perMinute(30);
            }

            // Different limits based on endpoint
            $endpoint = $request->segment(2);
            switch ($endpoint) {
                case 'metrics':
                    return Limit::perMinute(10)
                        ->by($request->user()?->id ?: $request->ip());
                case 'database':
                case 'network':
                    return Limit::perMinute(15)
                        ->by($request->ip());
                default:
                    return Limit::perMinute(20)
                        ->by($request->ip());
            }
        });

        // API Rate Limiter
        RateLimiter::for('api', function (Request $request) {
            // Higher limits for authenticated users
            if ($request->user()) {
                if ($request->user()->isAdmin()) {
                    return Limit::perMinute(120);
                }
                return Limit::perMinute(60);
            }

            // Lower limits for guest users
            return Limit::perMinute(30)->by($request->ip());
        });

        // Authentication Rate Limiter
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Driver API Rate Limiter
        RateLimiter::for('driver-api', function (Request $request) {
            $driver = $request->user();
            
            // Location updates can be more frequent
            if ($request->is('*/location')) {
                return Limit::perMinute(120)->by($driver?->id ?: $request->ip());
            }

            // Status updates
            if ($request->is('*/status')) {
                return Limit::perMinute(60)->by($driver?->id ?: $request->ip());
            }

            // Default driver endpoints
            return Limit::perMinute(30)->by($driver?->id ?: $request->ip());
        });

        // Admin API Rate Limiter
        RateLimiter::for('admin-api', function (Request $request) {
            $admin = $request->user();

            // Higher limits for super admins
            if ($admin?->isSuperAdmin()) {
                return Limit::perMinute(180);
            }

            // Regular admin limits
            return Limit::perMinute(120)->by($admin?->id ?: $request->ip());
        });

        // File Upload Rate Limiter
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Report Generation Rate Limiter
        RateLimiter::for('reports', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        // Webhook Rate Limiter
        RateLimiter::for('webhooks', function (Request $request) {
            $webhookToken = $request->header('X-Webhook-Token');
            return Limit::perMinute(30)->by($webhookToken ?: $request->ip());
        });
    }
}
