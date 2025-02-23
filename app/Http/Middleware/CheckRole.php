<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Allow if user has any of the required roles
        if (empty($roles) || in_array(auth()->user()->role, $roles)) {
            return $next($request);
        }

        // Log unauthorized access attempt
        \Log::warning('Unauthorized access attempt', [
            'user_id' => auth()->id(),
            'required_roles' => $roles,
            'user_role' => auth()->user()->role,
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Unauthorized. Required roles: ' . implode(', ', $roles)
            ], 403);
        }

        // Redirect based on user's current role
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access that page.');
        }

        if (auth()->user()->role === 'driver') {
            return redirect()->route('driver.dashboard')
                ->with('error', 'You do not have permission to access that page.');
        }

        return redirect()->route('login')
            ->with('error', 'You do not have permission to access that page.');
    }
}
