<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | List of origins that are allowed to access the API.
    | Wildcards are supported, e.g., '*.example.com'
    | Use '*' to allow any origin (not recommended for production)
    |
    */
    'allowed_origins' => env('CORS_ALLOWED_ORIGINS', implode(',', [
        'http://localhost:3000',      // React development server
        'http://localhost:8000',      // Laravel development server
        'http://127.0.0.1:8000',     // Laravel alternative development server
        'http://localhost:8080',      // Vue development server
        'http://localhost:4200',      // Angular development server
        'capacitor://localhost',      // Ionic/Capacitor mobile app
        'ionic://localhost',          // Ionic legacy
        'http://localhost',           // General local development
    ])),

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    |
    | List of HTTP methods that are allowed.
    |
    */
    'allowed_methods' => [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    |
    | List of headers that are allowed to be sent with requests.
    |
    */
    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Accept',
        'X-CSRF-TOKEN',
        'X-Socket-Id',
        'X-Device-Token',
        'X-Firebase-Token',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | List of headers that are allowed to be exposed to the web browser.
    |
    */
    'exposed_headers' => [
        'Cache-Control',
        'Content-Language',
        'Content-Type',
        'Expires',
        'Last-Modified',
        'Pragma',
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
        'X-RateLimit-Reset',
    ],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    |
    | Maximum number of seconds the CORS preflight response should be cached.
    |
    */
    'max_age' => env('CORS_MAX_AGE', 86400),

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | Whether the request can include user credentials like cookies, 
    | HTTP authentication or client side SSL certificates.
    |
    */
    'supports_credentials' => true,

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins Patterns
    |--------------------------------------------------------------------------
    |
    | Regular expressions that match origins that are allowed to access the API.
    | These are checked after the allowed_origins list.
    |
    */
    'allowed_origins_patterns' => [
        // Example: '#^https://.*\.example\.com$#'
    ],

    /*
    |--------------------------------------------------------------------------
    | Protected Domains
    |--------------------------------------------------------------------------
    |
    | List of domains that should always be protected, regardless of CORS settings.
    | These domains will never be allowed as origins.
    |
    */
    'protected_domains' => [
        'example.com',
        'api.example.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | When true, detailed CORS error messages will be added to the response.
    | This should be disabled in production.
    |
    */
    'debug' => env('CORS_DEBUG', env('APP_DEBUG', false)),

    /*
    |--------------------------------------------------------------------------
    | Path Configuration
    |--------------------------------------------------------------------------
    |
    | Configure CORS for specific paths. This overrides the default configuration.
    |
    */
    'paths' => [
        'api/*' => [
            'allowed_origins' => ['*'],
            'allowed_methods' => ['*'],
            'allowed_headers' => ['*'],
            'exposed_headers' => [],
            'max_age' => 0,
            'supports_credentials' => false,
        ],
        'sanctum/csrf-cookie' => [
            'allowed_origins' => ['*'],
            'allowed_methods' => ['GET', 'HEAD'],
            'allowed_headers' => ['*'],
            'exposed_headers' => [],
            'max_age' => 0,
            'supports_credentials' => true,
        ],
        'driver/*' => [
            'allowed_origins' => ['*'],
            'allowed_methods' => ['*'],
            'allowed_headers' => ['*'],
            'exposed_headers' => [],
            'max_age' => 0,
            'supports_credentials' => true,
        ],
    ],
];
