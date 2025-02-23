<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Closure;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Webhook endpoints
        'webhooks/*',
        // API endpoints that use token authentication
        'api/*',
        // Payment gateway callbacks
        'payment/callback/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            // Log CSRF token mismatch
            Log::warning('CSRF token mismatch', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => auth()->id(),
                'headers' => $request->headers->all(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'CSRF token mismatch.',
                    'error' => 'csrf_token_mismatch'
                ], 419);
            }

            // Redirect back with error message
            return redirect()
                ->back()
                ->withInput($request->except('_token', 'password', 'password_confirmation'))
                ->with('error', 'Your session has expired. Please try again.');
        }
    }

    /**
     * Determine if the request has a URI that should be excluded from CSRF verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        // Always pass through health check endpoints
        if ($request->is('health*')) {
            return true;
        }

        return parent::shouldPassThrough($request);
    }

    /**
     * Add the CSRF token to the response headers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return void
     */
    protected function addCookieToResponse($request, $response)
    {
        parent::addCookieToResponse($request, $response);

        // Add CSRF token to response headers for JavaScript access
        $response->headers->set('X-CSRF-TOKEN', $request->session()->token());
    }
}
