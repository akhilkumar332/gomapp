<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for various webhook integrations.
    | Each webhook can have its own security settings, retry policies, and handlers.
    |
    */

    'firebase' => [
        'enabled' => env('FIREBASE_WEBHOOKS_ENABLED', true),
        'secret' => env('FIREBASE_WEBHOOK_SECRET'),
        'verify_signature' => env('FIREBASE_WEBHOOK_VERIFY_SIGNATURE', true),
        'allowed_ips' => explode(',', env('FIREBASE_WEBHOOK_ALLOWED_IPS', '')),
        'events' => [
            'user.created',
            'user.deleted',
            'phone.verified',
        ],
        'retry' => [
            'max_attempts' => 3,
            'delay' => 5, // minutes
        ],
    ],

    'stripe' => [
        'enabled' => env('STRIPE_WEBHOOKS_ENABLED', true),
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
        'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300), // 5 minutes
        'events' => [
            'payment_intent.succeeded',
            'payment_intent.payment_failed',
            'customer.subscription.updated',
            'customer.subscription.deleted',
        ],
        'retry' => [
            'max_attempts' => 3,
            'delay' => 5, // minutes
        ],
    ],

    'twilio' => [
        'enabled' => env('TWILIO_WEBHOOKS_ENABLED', true),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'verify_signature' => env('TWILIO_WEBHOOK_VERIFY_SIGNATURE', true),
        'allowed_ips' => explode(',', env('TWILIO_WEBHOOK_ALLOWED_IPS', '')),
        'events' => [
            'MessageStatus',
            'CallStatus',
        ],
        'retry' => [
            'max_attempts' => 3,
            'delay' => 5, // minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Webhook Settings
    |--------------------------------------------------------------------------
    */
    'global' => [
        // Maximum payload size in bytes (10MB)
        'max_payload_size' => env('WEBHOOK_MAX_PAYLOAD_SIZE', 10 * 1024 * 1024),

        // Store raw webhook payloads for debugging
        'store_payloads' => env('WEBHOOK_STORE_PAYLOADS', false),

        // How long to keep webhook logs (in days)
        'log_retention' => env('WEBHOOK_LOG_RETENTION', 30),

        // Queue settings for webhook processing
        'queue' => [
            'connection' => env('WEBHOOK_QUEUE_CONNECTION', 'redis'),
            'queue' => env('WEBHOOK_QUEUE_NAME', 'webhooks'),
            'timeout' => env('WEBHOOK_QUEUE_TIMEOUT', 60),
            'tries' => env('WEBHOOK_QUEUE_TRIES', 3),
            'backoff' => [
                'seconds' => env('WEBHOOK_QUEUE_BACKOFF', 60),
                'tries' => env('WEBHOOK_QUEUE_MAX_TRIES', 3),
            ],
        ],

        // Rate limiting settings
        'rate_limiting' => [
            'enabled' => env('WEBHOOK_RATE_LIMITING_ENABLED', true),
            'max_attempts' => env('WEBHOOK_RATE_LIMITING_MAX_ATTEMPTS', 60),
            'decay_minutes' => env('WEBHOOK_RATE_LIMITING_DECAY_MINUTES', 1),
        ],

        // IP filtering
        'ip_filtering' => [
            'enabled' => env('WEBHOOK_IP_FILTERING_ENABLED', true),
            'allowed_ips' => explode(',', env('WEBHOOK_ALLOWED_IPS', '')),
            'blocked_ips' => explode(',', env('WEBHOOK_BLOCKED_IPS', '')),
        ],

        // Security headers that must be present
        'required_headers' => [
            'User-Agent',
            'Content-Type',
        ],

        // Response settings
        'response' => [
            // Whether to include debug information in responses
            'include_debug' => env('WEBHOOK_RESPONSE_INCLUDE_DEBUG', false),
            
            // Default response status code for successful processing
            'success_code' => env('WEBHOOK_RESPONSE_SUCCESS_CODE', 200),
            
            // Whether to send detailed error messages
            'detailed_errors' => env('WEBHOOK_RESPONSE_DETAILED_ERRORS', false),
        ],

        // Monitoring and alerting
        'monitoring' => [
            'enabled' => env('WEBHOOK_MONITORING_ENABLED', true),
            'alert_on_failure' => env('WEBHOOK_ALERT_ON_FAILURE', true),
            'alert_threshold' => env('WEBHOOK_ALERT_THRESHOLD', 5),
            'alert_interval' => env('WEBHOOK_ALERT_INTERVAL', 60), // minutes
            'notification_channels' => explode(',', env('WEBHOOK_NOTIFICATION_CHANNELS', 'mail,slack')),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Processing Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be run on all incoming webhooks.
    |
    */
    'middleware' => [
        'global' => [
            \App\Http\Middleware\VerifyWebhookSignature::class,
            \App\Http\Middleware\ValidateWebhookPayload::class,
        ],
        'firebase' => [
            \App\Http\Middleware\VerifyFirebaseWebhook::class,
        ],
        'stripe' => [
            \App\Http\Middleware\VerifyStripeWebhook::class,
        ],
        'twilio' => [
            \App\Http\Middleware\VerifyTwilioWebhook::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Event Handlers
    |--------------------------------------------------------------------------
    |
    | Map webhook events to their respective handler classes.
    |
    */
    'handlers' => [
        'firebase' => [
            'user.created' => \App\Handlers\Firebase\UserCreatedHandler::class,
            'user.deleted' => \App\Handlers\Firebase\UserDeletedHandler::class,
            'phone.verified' => \App\Handlers\Firebase\PhoneVerifiedHandler::class,
        ],
        'stripe' => [
            'payment_intent.succeeded' => \App\Handlers\Stripe\PaymentSucceededHandler::class,
            'payment_intent.payment_failed' => \App\Handlers\Stripe\PaymentFailedHandler::class,
            'customer.subscription.updated' => \App\Handlers\Stripe\SubscriptionUpdatedHandler::class,
            'customer.subscription.deleted' => \App\Handlers\Stripe\SubscriptionDeletedHandler::class,
        ],
        'twilio' => [
            'MessageStatus' => \App\Handlers\Twilio\MessageStatusHandler::class,
            'CallStatus' => \App\Handlers\Twilio\CallStatusHandler::class,
        ],
    ],
];
