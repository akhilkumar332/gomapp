<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Cookies that don't need encryption
        'theme',
        'sidebar_collapsed',
        'locale',
        'cookie_consent',
        'remember_web_*', // Laravel's remember me tokens
        'XSRF-TOKEN', // CSRF token cookie
    ];

    /**
     * Indicates if cookies should be serialized.
     *
     * @var bool
     */
    protected static $serialize = true;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (\Exception $e) {
            // Log encryption/decryption errors
            \Log::error('Cookie encryption error', [
                'error' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Clear problematic cookies
            $this->clearProblematicCookies($request);

            return $next($request);
        }
    }

    /**
     * Clear cookies that might be causing encryption issues.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function clearProblematicCookies($request)
    {
        $cookies = $request->cookies->all();
        
        foreach ($cookies as $name => $value) {
            if (!in_array($name, $this->except)) {
                \Cookie::forget($name);
            }
        }
    }

    /**
     * Determine if the cookie should be encrypted.
     *
     * @param  string  $name
     * @return bool
     */
    protected function isDisabled($name)
    {
        // Never encrypt session cookie
        if ($name === config('session.cookie')) {
            return true;
        }

        return parent::isDisabled($name);
    }
}
