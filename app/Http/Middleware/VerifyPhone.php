<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyPhone
{
    /**
     * Routes that can be accessed without phone verification
     *
     * @var array
     */
    protected $except = [
        'driver.verify-phone',
        'driver.resend-verification',
        'driver.logout',
        'driver.profile',
        'driver.update-profile',
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
        $user = Auth::user();

        // Skip verification for non-driver users
        if (!$user->isDriver()) {
            return $next($request);
        }

        // Skip verification for excepted routes
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        // Check if phone is verified
        if (!$user->hasVerifiedPhone()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Phone number not verified.',
                    'verification_required' => true,
                    'phone_number' => $user->phone_number
                ], 403);
            }

            return redirect()->route('driver.verify-phone')->with('warning', 'Please verify your phone number to continue.');
        }

        // Check if verification has expired (if configured)
        $verificationExpiry = config('auth.phone_verification_expiry');
        if ($verificationExpiry && 
            $user->phone_verified_at && 
            $user->phone_verified_at->addDays($verificationExpiry)->isPast()) {
            
            // Reset verification status
            $user->update([
                'phone_verified' => false,
                'phone_verified_at' => null
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Phone verification has expired. Please verify your phone number again.',
                    'verification_required' => true,
                    'phone_number' => $user->phone_number
                ], 403);
            }

            return redirect()->route('driver.verify-phone')
                ->with('warning', 'Your phone verification has expired. Please verify your phone number again.');
        }

        // Check if phone number has changed since last verification
        if ($user->phone_number_changed_at && 
            (!$user->phone_verified_at || $user->phone_number_changed_at->gt($user->phone_verified_at))) {
            
            // Reset verification status
            $user->update([
                'phone_verified' => false,
                'phone_verified_at' => null
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Phone number has changed. Please verify your new phone number.',
                    'verification_required' => true,
                    'phone_number' => $user->phone_number
                ], 403);
            }

            return redirect()->route('driver.verify-phone')
                ->with('warning', 'Your phone number has changed. Please verify your new phone number.');
        }

        return $next($request);
    }

    /**
     * Determine if the request should pass through verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        $route = $request->route();
        if (!$route) {
            return false;
        }

        $routeName = $route->getName();
        if (!$routeName) {
            return false;
        }

        foreach ($this->except as $excluded) {
            if (str_ends_with($excluded, '*')) {
                $prefix = rtrim($excluded, '*');
                if (str_starts_with($routeName, $prefix)) {
                    return true;
                }
            } elseif ($routeName === $excluded) {
                return true;
            }
        }

        return false;
    }
}
