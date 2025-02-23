<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = [
        // AWS ELB and CloudFront
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
        // Cloudflare IPs
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = 
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        try {
            // Log suspicious proxy headers in production
            if (app()->environment('production') && $this->hasSupiciousHeaders($request)) {
                Log::warning('Suspicious proxy headers detected', [
                    'headers' => $this->getProxyHeaders($request),
                    'ip' => $request->ip(),
                    'real_ip' => $request->server('REMOTE_ADDR'),
                    'url' => $request->fullUrl(),
                ]);
            }

            return parent::handle($request, $next);
        } catch (\Exception $e) {
            Log::error('Error in TrustProxies middleware', [
                'error' => $e->getMessage(),
                'headers' => $this->getProxyHeaders($request),
                'ip' => $request->ip(),
            ]);

            return $next($request);
        }
    }

    /**
     * Check for suspicious proxy headers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function hasSupiciousHeaders(Request $request): bool
    {
        $headers = $this->getProxyHeaders($request);
        $realIp = $request->server('REMOTE_ADDR');

        // Check if forwarded IP is in trusted ranges
        if (isset($headers['HTTP_X_FORWARDED_FOR'])) {
            $forwardedIp = explode(',', $headers['HTTP_X_FORWARDED_FOR'])[0];
            if (!$this->isIpTrusted($realIp)) {
                return true;
            }
        }

        // Check for protocol mismatch
        if (isset($headers['HTTP_X_FORWARDED_PROTO']) &&
            $headers['HTTP_X_FORWARDED_PROTO'] !== $request->getScheme()) {
            return true;
        }

        return false;
    }

    /**
     * Get all proxy-related headers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function getProxyHeaders(Request $request): array
    {
        return array_filter($request->server->all(), function ($key) {
            return strpos($key, 'HTTP_X_FORWARDED_') === 0 ||
                   strpos($key, 'HTTP_CF_') === 0 ||
                   strpos($key, 'HTTP_X_CF_') === 0;
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Check if an IP address is in the trusted ranges.
     *
     * @param  string  $ip
     * @return bool
     */
    protected function isIpTrusted(string $ip): bool
    {
        if ($this->proxies === '*') {
            return true;
        }

        foreach ((array) $this->proxies as $proxy) {
            if ($this->ipInRange($ip, $proxy)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IP is in a CIDR range.
     *
     * @param  string  $ip
     * @param  string  $range
     * @return bool
     */
    protected function ipInRange(string $ip, string $range): bool
    {
        if (str_contains($range, '/')) {
            list($range, $netmask) = explode('/', $range, 2);
            $range_decimal = ip2long($range);
            $ip_decimal = ip2long($ip);
            $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
            $netmask_decimal = ~$wildcard_decimal;
            return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
        }

        return $ip === $range;
    }
}
