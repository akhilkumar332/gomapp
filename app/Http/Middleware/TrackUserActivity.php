<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\ActivityLog;

class TrackUserActivity
{
    /**
     * Routes that should not be tracked
     *
     * @var array
     */
    protected $excludedRoutes = [
        'login',
        'logout',
        '_debugbar.*',
        'sanctum.*',
        '*.json',
        '*.xml',
        '*.ico',
        'assets.*',
        'images.*',
    ];

    /**
     * Parameters that should be masked in logs
     *
     * @var array
     */
    protected $sensitiveParams = [
        'password',
        'password_confirmation',
        'current_password',
        'firebase_token',
        'device_token',
        'token',
        'api_key',
        'credit_card',
        'card_number',
        'cvv',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        // Process the request
        $response = $next($request);

        if (Auth::check() && !$this->shouldSkipTracking($request)) {
            $user = Auth::user();
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2); // Duration in milliseconds

            // Update user's last activity
            $this->updateLastActivity($user);

            // Track user's location if provided
            if ($request->has(['latitude', 'longitude'])) {
                $this->updateUserLocation($user, $request);
            }

            // Log the activity
            $this->logActivity($request, $response, $user, $duration);

            // Update online status
            $this->updateOnlineStatus($user);
        }

        return $response;
    }

    /**
     * Determine if the request should be tracked
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldSkipTracking(Request $request)
    {
        $route = $request->route();
        if (!$route) {
            return true;
        }

        $routeName = $route->getName();
        if (!$routeName) {
            return true;
        }

        foreach ($this->excludedRoutes as $excludedRoute) {
            if (str_ends_with($excludedRoute, '*')) {
                $prefix = rtrim($excludedRoute, '*');
                if (str_starts_with($routeName, $prefix)) {
                    return true;
                }
            } elseif ($routeName === $excludedRoute) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update user's last activity timestamp
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    protected function updateLastActivity($user)
    {
        $now = now();
        
        // Update the database every 5 minutes
        if (!$user->last_activity || $user->last_activity->diffInMinutes($now) >= 5) {
            $user->update(['last_activity' => $now]);
        }

        // Update cache more frequently (every 1 minute)
        Cache::put("user.{$user->id}.last_activity", $now, 60);
    }

    /**
     * Update user's location
     *
     * @param  \App\Models\User  $user
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function updateUserLocation($user, Request $request)
    {
        $user->update([
            'last_latitude' => $request->latitude,
            'last_longitude' => $request->longitude,
            'last_location_update' => now(),
        ]);
    }

    /**
     * Log the activity
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $response
     * @param  \App\Models\User  $user
     * @param  float  $duration
     * @return void
     */
    protected function logActivity(Request $request, $response, $user, $duration)
    {
        $route = $request->route();
        $method = $request->method();
        $path = $request->path();
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // Get sanitized input data
        $input = $this->sanitizeInput($request->input());

        // Get response status and data
        $statusCode = $response->getStatusCode();
        $responseData = null;

        if ($response->getContent() && 
            str_contains($response->headers->get('Content-Type', ''), 'application/json')) {
            $responseData = json_decode($response->getContent(), true);
            // Limit response data size
            $responseData = $this->limitArraySize($responseData);
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'route_name' => $route->getName(),
            'method' => $method,
            'path' => $path,
            'status' => $this->getActivityStatus($statusCode),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'duration' => $duration,
            'input' => $input,
            'response' => $responseData,
            'status_code' => $statusCode,
            'location' => [
                'latitude' => $user->last_latitude,
                'longitude' => $user->last_longitude,
            ],
        ]);
    }

    /**
     * Update user's online status
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    protected function updateOnlineStatus($user)
    {
        $cacheKey = "user.{$user->id}.online";
        
        Cache::put($cacheKey, true, Carbon::now()->addMinutes(5));

        // Broadcast user's online status if needed
        if ($user->isDriver() && $user->device_token) {
            broadcast(new UserOnline($user));
        }
    }

    /**
     * Sanitize input data by removing sensitive information
     *
     * @param  array  $input
     * @return array
     */
    protected function sanitizeInput($input)
    {
        if (!is_array($input)) {
            return $input;
        }

        foreach ($input as $key => $value) {
            if (in_array($key, $this->sensitiveParams)) {
                $input[$key] = '******';
            } elseif (is_array($value)) {
                $input[$key] = $this->sanitizeInput($value);
            }
        }

        return $this->limitArraySize($input);
    }

    /**
     * Limit array size to prevent huge logs
     *
     * @param  array|null  $data
     * @param  int  $maxDepth
     * @return array|null
     */
    protected function limitArraySize($data, $maxDepth = 3)
    {
        if (!is_array($data)) {
            return $data;
        }

        if ($maxDepth <= 0) {
            return '[Array]';
        }

        $result = [];
        $count = 0;
        foreach ($data as $key => $value) {
            if ($count >= 10) {
                $result['...'] = 'Array truncated';
                break;
            }

            if (is_array($value)) {
                $result[$key] = $this->limitArraySize($value, $maxDepth - 1);
            } else {
                $result[$key] = $value;
            }

            $count++;
        }

        return $result;
    }

    /**
     * Get activity status based on response code
     *
     * @param  int  $statusCode
     * @return string
     */
    protected function getActivityStatus($statusCode)
    {
        if ($statusCode >= 500) {
            return 'error';
        }
        if ($statusCode >= 400) {
            return 'warning';
        }
        if ($statusCode >= 200 && $statusCode < 300) {
            return 'success';
        }
        return 'info';
    }
}
