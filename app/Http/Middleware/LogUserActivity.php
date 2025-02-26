<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogUserActivity
{
    /**
     * Protected routes that should not be logged
     *
     * @var array
     */
    protected $excludedRoutes = [
        'admin.dashboard',
        'driver.dashboard',
        'login',
        'logout',
        '_debugbar.*',
        'sanctum.*',
    ];

    /**
     * Protected parameters that should be masked in logs
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
        // Process the request
        $response = $next($request);

        // Don't log excluded routes
        if ($this->shouldSkipLogging($request)) {
            return $response;
        }

        try {
            $this->logActivity($request, $response);
        } catch (\Exception $e) {
            \Log::error('Failed to log user activity: ' . $e->getMessage());
        }

        return $response;
    }

    /**
     * Determine if the request should be logged
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldSkipLogging(Request $request)
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
     * Log the activity
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $response
     * @return void
     */
    protected function logActivity(Request $request, $response)
    {
        $user = Auth::user();
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

        // Create activity log
        ActivityLog::create([
            'user_id' => $user ? $user->id : null,
            'user_type' => $user ? $user->role : null,
            'route_name' => $route->getName(),
            'method' => $method,
            'path' => $path,
            'status' => $this->getActivityStatus($statusCode),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'input' => $input,
            'response' => $responseData,
            'status_code' => $statusCode,
            'description' => $this->generateDescription($method, $route->getName()),
            'properties' => [
                'route_parameters' => $route->parameters(),
                'headers' => $this->getRelevantHeaders($request),
                'session_id' => session()->getId(),
            ]
        ]);
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
     * Get relevant headers for logging
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function getRelevantHeaders(Request $request)
    {
        $relevantHeaders = [
            'accept',
            'accept-language',
            'accept-encoding',
            'referer',
            'user-agent',
            'origin',
            'content-type',
        ];

        $headers = [];
        foreach ($relevantHeaders as $header) {
            if ($request->headers->has($header)) {
                $headers[$header] = $request->headers->get($header);
            }
        }

        return $headers;
    }

    /**
     * Generate human-readable description of the activity
     *
     * @param  string  $method
     * @param  string  $routeName
     * @return string
     */
    protected function generateDescription($method, $routeName)
    {
        $parts = explode('.', $routeName);
        $action = end($parts);
        $resource = $parts[count($parts) - 2] ?? '';

        switch ($method) {
            case 'GET':
                return "Viewed $resource" . ($action !== 'index' ? " $action" : 's');
            case 'POST':
                return "Created new $resource";
            case 'PUT':
            case 'PATCH':
                return "Updated $resource";
            case 'DELETE':
                return "Deleted $resource";
            default:
                return "Performed $method on $resource";
        }
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
