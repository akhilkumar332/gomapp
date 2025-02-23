<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for Firebase integration.
    | These settings are used by the FirebaseService to handle authentication,
    | push notifications, and other Firebase-related functionality.
    |
    */

    'enabled' => env('FIREBASE_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Firebase Project Configuration
    |--------------------------------------------------------------------------
    */
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'api_key' => env('FIREBASE_API_KEY'),
    'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
    'database_url' => env('FIREBASE_DATABASE_URL'),
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
    'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
    'app_id' => env('FIREBASE_APP_ID'),
    'measurement_id' => env('FIREBASE_MEASUREMENT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Service Account
    |--------------------------------------------------------------------------
    |
    | Path to the Firebase service account JSON file relative to storage/app
    |
    */
    'service_account' => env('FIREBASE_SERVICE_ACCOUNT', 'firebase/service-account.json'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Dynamic Links
    |--------------------------------------------------------------------------
    */
    'dynamic_links' => [
        'domain_uri_prefix' => env('FIREBASE_DYNAMIC_LINKS_PREFIX'),
        'android_package_name' => env('FIREBASE_ANDROID_PACKAGE_NAME'),
        'ios_bundle_id' => env('FIREBASE_IOS_BUNDLE_ID'),
        'ios_app_store_id' => env('FIREBASE_IOS_APP_STORE_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging
    |--------------------------------------------------------------------------
    */
    'fcm' => [
        'default_topic' => env('FIREBASE_FCM_DEFAULT_TOPIC', 'general'),
        'driver_topic' => env('FIREBASE_FCM_DRIVER_TOPIC', 'drivers'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Authentication
    |--------------------------------------------------------------------------
    */
    'auth' => [
        'tenant_id' => env('FIREBASE_AUTH_TENANT_ID'),
        'email_verification' => env('FIREBASE_AUTH_EMAIL_VERIFICATION', false),
        'phone_verification' => env('FIREBASE_AUTH_PHONE_VERIFICATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Storage
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'default_bucket' => env('FIREBASE_STORAGE_BUCKET'),
        'temp_url_expiration' => env('FIREBASE_STORAGE_TEMP_URL_EXPIRATION', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Database
    |--------------------------------------------------------------------------
    */
    'database' => [
        'url' => env('FIREBASE_DATABASE_URL'),
        'auth_variable_override' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Admin SDK
    |--------------------------------------------------------------------------
    */
    'credentials' => [
        'file' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/service-account.json')),
        'auto_discovery' => env('FIREBASE_CREDENTIALS_AUTO_DISCOVERY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Client SDK
    |--------------------------------------------------------------------------
    */
    'client' => [
        'api_key' => env('FIREBASE_API_KEY'),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
        'database_url' => env('FIREBASE_DATABASE_URL'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
        'app_id' => env('FIREBASE_APP_ID'),
        'measurement_id' => env('FIREBASE_MEASUREMENT_ID'),
    ],
];
