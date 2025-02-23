<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HandleCors
{
    /**
     * Allowed origins for CORS requests.
     * Use environment variables to configure these.
     *
     * @var array
     */
    protected $allowedOrigins;

    /**
     * Allowed methods for CORS requests.
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
     * Allowed headers for CORS requests.
     *
     * @var array
     */
    protected $allowedHeaders = [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Accept',
        'X-CSRF-TOKEN',
        'X-Socket-Id',
        'X-Device-Token',
        'X-Firebase-Token',
    ];

    /**
     * Headers that are allowed to be exposed to the web browser.
     *
     * @var array
     */
    protected $exposedHeaders = [
        'Cache-Control',
        'Content-Language',
        'Content-Type',
        'Expires',
        'Last-Modified',
        'Pragma',
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
        'X-RateLimit-Reset',
    ];

    /**
     * Create a new middleware instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->allowedOrigins = $this->parseAllowedOrigins();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // If the request is not from an allowed origin, proceed without CORS headers
        if (!$this->isAllowedOrigin($request->header('Origin'))) {
            return $next($request);
        }

        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            return $this->handlePreflightRequest($request);
        }

        // Handle actual request
        $response = $next($request);

        return $this->addCorsHeaders($response, $request);
    }

    /**
     * Parse allowed origins from environment variables.
     *
     * @return array
     */
    protected function parseAllowedOrigins()
    {
        $origins = explode(',', config('cors.allowed_origins', '*'));
        
        return array_map(function ($origin) {
            return trim($origin);
        }, $origins);
    }

    /**
     * Determine if the origin is allowed.
     *
     * @param  string|null  $origin
     * @return bool
     */
    protected function isAllowedOrigin($origin)
    {
        if (empty($origin)) {
            return false;
        }

        if (in_array('*', $this->allowedOrigins)) {
            return true;
        }

        return in_array($origin, $this->allowedOrigins) || 
               $this->isAllowedWildcardOrigin($origin);
    }

    /**
     * Determine if the origin matches any wildcard allowed origins.
     *
     * @param  string  $origin
     * @return bool
     */
    protected function isAllowedWildcardOrigin($origin)
    {
        foreach ($this->allowedOrigins as $allowedOrigin) {
            if (Str::is($allowedOrigin, $origin)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle preflight request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function handlePreflightRequest($request)
    {
        $headers = [
            'Access-Control-Allow-Origin' => $request->header('Origin'),
            'Access-Control-Allow-Methods' => implode(', ', $this->allowedMethods),
            'Access-Control-Allow-Headers' => implode(', ', $this->allowedHeaders),
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '86400',
            'Content-Length' => '0',
        ];

        return response(null, 204, $headers);
    }

    /**
     * Add CORS headers to the response.
     *
     * @param  \Illuminate\Http\Response  $response
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function addCorsHeaders($response, $request)
    {
        $response->headers->set('Access-Control-Allow-Origin', $request->header('Origin'));
        $response->headers->set('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods));
        $response->headers->set('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders));
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Expose-Headers', implode(', ', $this->exposedHeaders));

        return $response;
    }

    /**
     * Get the maximum age for CORS preflight requests.
     *
     * @return int
     */
    protected function getMaxAge()
    {
        return config('cors.max_age', 86400);
    }
}
