<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
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

        if (!Auth::user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
            }
            return redirect()->route('login')->with('error', 'Unauthorized. Admin access required.');
        }

        // Log admin activity only if the route has a name
        try {
            if ($request->route() && $request->route()->getName()) {
                ActivityLog::log(
                    'admin_route_access',
                    'Admin route accessed: ' . $request->route()->getName()
                );
            }
        } catch (\Exception $e) {
            // Log silently failed, continue with the request
            report($e);
        }

        return $next($request);
    }
}
