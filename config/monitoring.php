<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Health Check Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the health check settings for your application.
    | The check interval determines how often the system will run checks,
    | while the timeout sets the maximum time for each check to complete.
    |
    */

    'check_interval' => env('HEALTH_CHECK_INTERVAL', 5), // minutes
    'timeout' => env('HEALTH_CHECK_TIMEOUT', 30), // seconds

    /*
    |--------------------------------------------------------------------------
    | Monitoring Components
    |--------------------------------------------------------------------------
    |
    | Configure which components of your application should be monitored
    | and how they should be checked.
    |
    */

    'monitors' => [
        'api' => [
            'enabled' => true,
            'exclude_patterns' => [
                '/health',
                '/status',
                '_ignition/*',
                'sanctum/*',
                'api-docs/*'
            ],
            'groups' => [
                'admin' => [
                    'pattern' => 'api/admin/*',
                    'label' => 'Admin API'
                ],
                'driver' => [
                    'pattern' => 'api/driver/*',
                    'label' => 'Driver API'
                ],
                'public' => [
                    'pattern' => 'api/*',
                    'label' => 'Public API'
                ]
            ],
            'metrics' => [
                'response_time',
                'error_rate',
                'request_rate'
            ]
        ],

        'web' => [
            'enabled' => true,
            'urls' => [
                'main' => env('APP_URL'),
                'admin' => env('APP_URL') . '/admin',
                'driver' => env('APP_URL') . '/driver'
            ],
            'metrics' => [
                'response_time',
                'error_rate',
                'uptime'
            ]
        ],

        'database' => [
            'enabled' => true,
            'connections' => [
                'sqlite' => [
                    'name' => 'SQLite',
                    'connection' => 'sqlite'
                ]
            ],
            'metrics' => [
                'connection_time',
                'query_time',
                'active_connections'
            ],
            'thresholds' => [
                'connection_time' => 1000, // milliseconds
                'query_time' => 500 // milliseconds
            ]
        ],

        'cache' => [
            'enabled' => true,
            'stores' => [
                'file',
                'array'
            ],
            'metrics' => [
                'hit_rate',
                'miss_rate',
                'size'
            ]
        ],

        'storage' => [
            'enabled' => true,
            'disks' => [
                'local',
                'public'
            ],
            'metrics' => [
                'disk_usage',
                'disk_free',
                'write_permission'
            ],
            'thresholds' => [
                'disk_usage_warning' => 75, // percentage
                'disk_usage_critical' => 90 // percentage
            ]
        ],

        'queue' => [
            'enabled' => true,
            'metrics' => [
                'failed_jobs',
                'pending_jobs',
                'processed_jobs'
            ],
            'thresholds' => [
                'max_failed_jobs' => 100,
                'max_pending_jobs' => 1000
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure how and when notifications should be sent for various events.
    |
    */

    'notifications' => [
        'channels' => [
            'mail' => [
                'enabled' => env('HEALTH_CHECK_MAIL_ENABLED', false),
                'to' => env('HEALTH_CHECK_MAIL', null),
            ],
            'slack' => [
                'enabled' => env('HEALTH_CHECK_SLACK_ENABLED', false),
                'webhook' => env('HEALTH_CHECK_SLACK_WEBHOOK', null),
            ]
        ],
        'events' => [
            'status_changed' => true,
            'threshold_exceeded' => true,
            'service_down' => true
        ],
        'throttle' => [
            'enabled' => true,
            'attempts' => 3,
            'decay_minutes' => 5
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how monitoring events should be logged.
    |
    */

    'logging' => [
        'enabled' => true,
        'channel' => env('HEALTH_CHECK_LOG_CHANNEL', 'stack'),
        'level' => env('HEALTH_CHECK_LOG_LEVEL', 'error'),
        'separate_channel' => false
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how monitoring results should be cached.
    |
    */

    'cache' => [
        'enabled' => true,
        'key' => 'health-status',
        'ttl' => 300 // 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | History Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how monitoring history should be maintained.
    |
    */

    'history' => [
        'enabled' => true,
        'retention_days' => 7,
        'prune_after' => 1000 // records
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the monitoring dashboard UI settings.
    |
    */

    'ui' => [
        'refresh_interval' => 30, // seconds
        'date_format' => 'Y-m-d H:i:s',
        'theme' => 'light', // light, dark, or auto
        'show_details' => true
    ]
];
