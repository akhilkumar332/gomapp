<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Log unauthorized access attempt
        \Log::warning('Unauthorized access attempt', [
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Store intended URL for redirect after login
        if (!$request->is('admin/*', 'driver/*')) {
            session()->put('url.intended', $request->url());
        }

        // Add a flash message
        session()->flash('error', 'Please login to continue.');

        return route('login');
    }
}
