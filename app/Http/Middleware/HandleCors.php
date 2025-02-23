<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    /**
     * Allowed origins for CORS requests.
     * 
     * @var array
     */
    protected $allowedOrigins = [
        'http://localhost:3000',
        'http://localhost:8000',
        'https://delivery-management.com',
        'https://*.delivery-management.com',
    ];

    /**
     * Allowed HTTP methods.
     * 
     * @var array
     */
    protected $allowedMethods = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS',
    ];

    /**
     * Allowed headers.
     * 
     * @var array
     */
    protected $allowedHeaders = [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'X-CSRF-TOKEN',
        'Accept',
        'Origin',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Handle preflight OPTIONS request
            if ($request->isMethod('OPTIONS')) {
                return $this->handlePreflightRequest($request);
            }

            // Get the origin
            $origin = $request->header('Origin');

            // If no origin header is present, allow the request
            if (!$origin) {
                return $next($request);
            }

            // Check if origin is allowed
            if (!$this->isOriginAllowed($origin)) {
                // Log unauthorized CORS attempt
                Log::warning('Unauthorized CORS attempt', [
                    'origin' => $origin,
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ]);

                return response()->json([
                    'message' => 'Origin not allowed'
                ], 403);
            }

            $response = $next($request);

            // Add CORS headers to response
            return $this->addCorsHeaders($response, $origin);
        } catch (\Exception $e) {
            Log::error('CORS handling error', [
                'error' => $e->getMessage(),
                'origin' => $request->header('Origin'),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'CORS error occurred'
            ], 500);
        }
    }

    /**
     * Handle preflight OPTIONS request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function handlePreflightRequest(Request $request)
    {
        $origin = $request->header('Origin');
        
        if (!$this->isOriginAllowed($origin)) {
            return response()->json(['message' => 'Origin not allowed'], 403);
        }

        $headers = [
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Methods' => implode(', ', $this->allowedMethods),
            'Access-Control-Allow-Headers' => implode(', ', $this->allowedHeaders),
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '86400', // 24 hours
        ];

        return response()->json(['message' => 'OK'], 200, $headers);
    }

    /**
     * Add CORS headers to response.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @param  string  $origin
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function addCorsHeaders($response, $origin)
    {
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods));
        $response->headers->set('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders));
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Vary', 'Origin');

        return $response;
    }

    /**
     * Check if the origin is allowed.
     *
     * @param  string|null  $origin
     * @return bool
     */
    protected function isOriginAllowed(?string $origin): bool
    {
        if (!$origin) {
            return false;
        }

        foreach ($this->allowedOrigins as $allowedOrigin) {
            // Handle wildcard subdomains
            if (str_contains($allowedOrigin, '*')) {
                $pattern = str_replace('*', '.*', preg_quote($allowedOrigin, '/'));
                if (preg_match('/^' . $pattern . '$/', $origin)) {
                    return true;
                }
            }
            // Exact match
            elseif ($allowedOrigin === $origin) {
                return true;
            }
        }

        return false;
    }
}
