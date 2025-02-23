<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use RuntimeException;

class CustomThrottleRequests
{
    /**
     * The rate limiter instance.
     *
     * @var \Illuminate\Cache\RateLimiter
     */
    protected $limiter;

    /**
     * Rate limits for different user roles (requests per minute).
     *
     * @var array
     */
    protected $roleLimits = [
        'admin' => [
            'attempts' => 120,
            'decay' => 1, // minutes
        ],
        'driver' => [
            'attempts' => 60,
            'decay' => 1,
        ],
        'default' => [
            'attempts' => 30,
            'decay' => 1,
        ],
    ];

    /**
     * Endpoints with custom rate limits.
     *
     * @var array
     */
    protected $customEndpointLimits = [
        'auth/*' => [
            'attempts' => 5,
            'decay' => 1,
        ],
        'api/auth/*' => [
            'attempts' => 5,
            'decay' => 1,
        ],
        'api/driver/location' => [
            'attempts' => 360, // Allow frequent location updates (every 10 seconds)
            'decay' => 1,
        ],
        'api/driver/status' => [
            'attempts' => 360,
            'decay' => 1,
        ],
    ];

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Cache\RateLimiter  $limiter
     * @return void
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int|string  $maxAttempts
     * @param  float|int  $decayMinutes
     * @param  string  $prefix
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        // Get rate limit based on user role and endpoint
        $rateLimit = $this->getRateLimit($request);
        
        if (is_string($maxAttempts) && func_num_args() === 3) {
            $prefix = func_get_args()[2];
        }

        $key = $prefix.$this->resolveRequestSignature($request);

        $maxAttempts = $rateLimit['attempts'];
        $decayMinutes = $rateLimit['decay'];

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            throw $this->buildException($key, $maxAttempts);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Get rate limit based on user role and endpoint.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function getRateLimit($request)
    {
        // Check for custom endpoint limits first
        foreach ($this->customEndpointLimits as $pattern => $limit) {
            if ($this->requestMatchesPattern($request, $pattern)) {
                return $limit;
            }
        }

        // Fall back to role-based limits
        if (Auth::check()) {
            $role = Auth::user()->role;
            return $this->roleLimits[$role] ?? $this->roleLimits['default'];
        }

        return $this->roleLimits['default'];
    }

    /**
     * Check if request path matches a pattern.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $pattern
     * @return bool
     */
    protected function requestMatchesPattern($request, $pattern)
    {
        $path = $request->path();
        
        if (str_ends_with($pattern, '*')) {
            $pattern = rtrim($pattern, '*');
            return str_starts_with($path, $pattern);
        }

        return $path === $pattern;
    }

    /**
     * Resolve request signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function resolveRequestSignature($request)
    {
        if ($user = $request->user()) {
            return sha1($user->getAuthIdentifier());
        } elseif ($route = $request->route()) {
            return sha1($request->ip().
                '|'.$request->userAgent().
                '|'.$route->getDomain().
                '|'.$route->uri().
                '|'.$request->method()
            );
        }

        throw new RuntimeException('Unable to generate the request signature. Route unavailable.');
    }

    /**
     * Create a 'too many attempts' exception.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function buildException($key, $maxAttempts)
    {
        $retryAfter = $this->getTimeUntilNextRetry($key);

        $headers = $this->getHeaders(
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter),
            $retryAfter
        );

        return new \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException(
            $retryAfter,
            'Too Many Attempts.',
            null,
            $headers
        );
    }

    /**
     * Get the number of seconds until the next retry.
     *
     * @param  string  $key
     * @return int
     */
    protected function getTimeUntilNextRetry($key)
    {
        return $this->limiter->availableIn($key);
    }

    /**
     * Calculate the number of remaining attempts.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @param  int|null  $retryAfter
     * @return int
     */
    protected function calculateRemainingAttempts($key, $maxAttempts, $retryAfter = null)
    {
        if (is_null($retryAfter)) {
            return $this->limiter->retriesLeft($key, $maxAttempts);
        }

        return 0;
    }

    /**
     * Get the limit headers information.
     *
     * @param  int  $maxAttempts
     * @param  int  $remainingAttempts
     * @param  int|null  $retryAfter
     * @return array
     */
    protected function getHeaders($maxAttempts, $remainingAttempts, $retryAfter = null)
    {
        $headers = [
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ];

        if (!is_null($retryAfter)) {
            $headers['Retry-After'] = $retryAfter;
            $headers['X-RateLimit-Reset'] = $this->availableAt($retryAfter);
        }

        return $headers;
    }

    /**
     * Add the limit header information to the given response.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @param  int  $maxAttempts
     * @param  int  $remainingAttempts
     * @param  int|null  $retryAfter
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function addHeaders(Response $response, $maxAttempts, $remainingAttempts, $retryAfter = null)
    {
        $response->headers->add(
            $this->getHeaders($maxAttempts, $remainingAttempts, $retryAfter)
        );

        return $response;
    }

    /**
     * Get the timestamp when the rate limit will be available.
     *
     * @param  int  $retryAfter
     * @return int
     */
    protected function availableAt($retryAfter)
    {
        return time() + $retryAfter;
    }
}
