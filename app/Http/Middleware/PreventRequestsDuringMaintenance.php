<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;
use Illuminate\Support\Facades\Auth;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be accessible while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [
        'login',
        'logout',
        'admin/*',
        'api/admin/*',
        'api/auth/*',
    ];

    /**
     * The roles that can bypass maintenance mode.
     *
     * @var array<int, string>
     */
    protected $bypassRoles = [
        'admin',
        'super_admin',
    ];

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->app->isDownForMaintenance()) {
            // Check if user can bypass maintenance mode
            if ($this->canBypassMaintenance($request)) {
                return $next($request);
            }

            // Check if the request is for an excepted URI
            if ($this->inExceptArray($request)) {
                return $next($request);
            }

            // Return maintenance mode response
            return $this->handleMaintenanceMode($request);
        }

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should be accessible in maintenance mode.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
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

    /**
     * Determine if the authenticated user can bypass maintenance mode.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function canBypassMaintenance($request)
    {
        // Check for maintenance mode bypass token in request
        $token = $request->header('X-Maintenance-Token') ?? $request->input('maintenance_token');
        if ($token && $token === config('app.maintenance_token')) {
            return true;
        }

        // Check for user role-based bypass
        if (Auth::check() && in_array(Auth::user()->role, $this->bypassRoles)) {
            return true;
        }

        // Check for specific IP addresses that can bypass
        $bypassIps = config('app.maintenance_bypass_ips', []);
        if (in_array($request->ip(), $bypassIps)) {
            return true;
        }

        return false;
    }

    /**
     * Handle the maintenance mode response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function handleMaintenanceMode($request)
    {
        $maintenanceMode = $this->app->maintenanceMode();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $maintenanceMode->getMessage() ?: 'Application is currently under maintenance.',
                'retry_after' => $maintenanceMode->getRetryAfter(),
                'status' => 'maintenance',
            ], 503);
        }

        // For web requests, return a view
        return response()->view('errors.maintenance', [
            'message' => $maintenanceMode->getMessage(),
            'retryAfter' => $maintenanceMode->getRetryAfter(),
            'whenAvailable' => now()->addSeconds($maintenanceMode->getRetryAfter())->diffForHumans(),
            'estimatedDuration' => $this->formatDuration($maintenanceMode->getRetryAfter()),
        ], 503);
    }

    /**
     * Format the maintenance mode duration into a human-readable string.
     *
     * @param  int  $seconds
     * @return string
     */
    protected function formatDuration($seconds)
    {
        if ($seconds < 60) {
            return $seconds . ' seconds';
        }

        if ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            return $minutes . ' ' . str_plural('minute', $minutes);
        }

        if ($seconds < 86400) {
            $hours = floor($seconds / 3600);
            return $hours . ' ' . str_plural('hour', $hours);
        }

        $days = floor($seconds / 86400);
        return $days . ' ' . str_plural('day', $days);
    }
}
