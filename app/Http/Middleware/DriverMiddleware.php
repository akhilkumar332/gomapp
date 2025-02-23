<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DriverMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'driver') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Driver access required.'], 403);
            }
            
            return redirect()->route('login')->with('error', 'Unauthorized. Driver access required.');
        }

        return $next($request);
    }
}
