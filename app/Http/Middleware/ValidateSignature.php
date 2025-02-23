<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Symfony\Component\HttpFoundation\Response;

class ValidateSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $relative = null): Response
    {
        try {
            if ($relative !== null) {
                if (!$request->hasValidRelativeSignature()) {
                    throw new InvalidSignatureException;
                }
            } elseif (!$request->hasValidSignature()) {
                throw new InvalidSignatureException;
            }
        } catch (InvalidSignatureException $e) {
            // Log invalid signature attempt
            \Log::warning('Invalid URL signature attempt', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => auth()->id()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Invalid URL signature.'
                ], 403);
            }

            abort(403, 'Invalid URL signature.');
        }

        return $next($request);
    }
}
