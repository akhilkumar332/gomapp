<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (auth()->check() && !$request->is('admin/activity-logs*')) {
            try {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => $this->getAction($request),
                    'description' => $this->getDescription($request),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'device_type' => $this->getDeviceType($request),
                    'route' => $request->route()->getName() ?? 'unnamed',
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'status_code' => $response->getStatusCode(),
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to log user activity: ' . $e->getMessage());
            }
        }

        return $response;
    }

    /**
     * Get the action description based on the request.
     */
    protected function getAction(Request $request): string
    {
        $method = strtoupper($request->method());
        $path = $request->path();

        if ($method === 'GET') {
            return 'viewed';
        }

        if ($method === 'POST') {
            if (str_contains($path, 'login')) {
                return 'logged in';
            }
            if (str_contains($path, 'logout')) {
                return 'logged out';
            }
            return 'created';
        }

        if ($method === 'PUT' || $method === 'PATCH') {
            return 'updated';
        }

        if ($method === 'DELETE') {
            return 'deleted';
        }

        return 'performed';
    }

    /**
     * Get a human-readable description of the action.
     */
    protected function getDescription(Request $request): string
    {
        $action = $this->getAction($request);
        $path = str_replace('/', ' ', $request->path());
        $path = ucwords($path);

        if ($action === 'viewed') {
            return "Viewed {$path} page";
        }

        if ($action === 'logged in') {
            return 'Logged into the system';
        }

        if ($action === 'logged out') {
            return 'Logged out of the system';
        }

        return ucfirst($action) . ' ' . $path;
    }

    /**
     * Determine the device type from the user agent.
     */
    protected function getDeviceType(Request $request): string
    {
        $userAgent = $request->userAgent();

        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($userAgent))) {
            return 'tablet';
        }

        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($userAgent))) {
            return 'mobile';
        }

        return 'desktop';
    }
}
