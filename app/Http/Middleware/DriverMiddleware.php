<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        if (!Auth::user()->isDriver()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Driver access required.'], 403);
            }
            return redirect()->route('login')->with('error', 'Unauthorized. Driver access required.');
        }

        $driver = Auth::user();

        // Check if driver is active
        if ($driver->status !== 'active') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account is not active. Please contact support.'], 403);
            }
            Auth::logout();
            return redirect()->route('login')->with('error', 'Account is not active. Please contact support.');
        }

        // Check if phone is verified for API requests
        if ($request->expectsJson() && !$driver->hasVerifiedPhone()) {
            return response()->json([
                'message' => 'Phone number not verified.',
                'verification_required' => true
            ], 403);
        }

        // Update last activity timestamp
        $driver->update([
            'last_activity' => now()
        ]);

        // Log driver activity
        activity()
            ->causedBy($driver)
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()->getName(),
                'method' => $request->method(),
                'last_latitude' => $driver->last_latitude,
                'last_longitude' => $driver->last_longitude,
            ])
            ->log('Driver route accessed');

        // Update location if provided in request
        if ($request->has(['latitude', 'longitude'])) {
            $driver->update([
                'last_latitude' => $request->latitude,
                'last_longitude' => $request->longitude,
                'last_location_update' => now()
            ]);
        }

        // Check for required device token
        if ($request->expectsJson() && !$driver->device_token && !$request->routeIs('driver.device-token.*')) {
            return response()->json([
                'message' => 'Device token required.',
                'device_token_required' => true
            ], 428);
        }

        // Add driver instance to request for controllers
        $request->merge(['driver' => $driver]);

        return $next($request);
    }
}
