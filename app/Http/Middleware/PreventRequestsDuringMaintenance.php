<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Health check endpoints
        'health',
        'health/*',
        // Admin routes for disabling maintenance mode
        'admin/maintenance/*',
        // API endpoints that should remain accessible
        'api/*/status',
        // Webhook endpoints
        'webhooks/*',
        // Assets
        'assets/*',
        'css/*',
        'js/*',
        'images/*',
        'fonts/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, \Closure $next)
    {
        try {
            if ($this->app->maintenanceMode()->active()) {
                $data = $this->app->maintenanceMode()->data();

                // Log maintenance mode access attempt
                Log::info('Maintenance mode access attempt', [
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'user_id' => auth()->id(),
                    'maintenance_data' => $data
                ]);

                if ($this->shouldPassThrough($request)) {
                    return $next($request);
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $data['message'] ?? 'Application is down for maintenance.',
                        'status' => 'maintenance',
                        'retry_after' => $data['retry'] ?? null,
                        'estimated_time' => $data['time'] ?? null
                    ], 503);
                }

                throw new HttpException(
                    503,
                    $data['message'] ?? 'Service Unavailable',
                    null,
                    [],
                    $data['retry'] ?? null
                );
            }

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Error in maintenance mode middleware', [
                'error' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Determine if the request has a URI that should be accessible in maintenance mode.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        // Allow admin users to bypass maintenance mode
        if (auth()->check() && auth()->user()->role === 'admin') {
            return true;
        }

        // Check if the request matches any except patterns
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
