<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Redirect based on user role
                $user = Auth::guard($guard)->user();
                
                // Get secure redirect URL based on role
                $redirectUrl = match($user->role) {
                    'admin' => url()->secure(route('admin.dashboard', [], false)),
                    'driver' => url()->secure(route('driver.dashboard', [], false)),
                    default => url()->secure(RouteServiceProvider::HOME),
                };

                return redirect()->to($redirectUrl);
            }
        }

        return $next($request);
    }
}
