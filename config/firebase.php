<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Service Account
    |--------------------------------------------------------------------------
    |
    | Path to the Firebase service account credentials JSON file.
    |
    */
    'credentials_path' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/service-account.json')),

    /*
    |--------------------------------------------------------------------------
    | Firebase Database URL
    |--------------------------------------------------------------------------
    |
    | The URL of your Firebase Realtime Database.
    |
    */
    'database_url' => env('FIREBASE_DATABASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Project ID
    |--------------------------------------------------------------------------
    |
    | Your Firebase project ID.
    |
    */
    'project_id' => env('FIREBASE_PROJECT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Storage Bucket
    |--------------------------------------------------------------------------
    |
    | Your Firebase Storage bucket URL.
    |
    */
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Web API Key
    |--------------------------------------------------------------------------
    |
    | Your Firebase Web API Key for client-side authentication.
    |
    */
    'api_key' => env('FIREBASE_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Auth Domain
    |--------------------------------------------------------------------------
    |
    | Your Firebase Authentication domain.
    |
    */
    'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Messaging Sender ID
    |--------------------------------------------------------------------------
    |
    | Your Firebase Cloud Messaging sender ID.
    |
    */
    'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),

    /*
    |--------------------------------------------------------------------------
    | Firebase App ID
    |--------------------------------------------------------------------------
    |
    | Your Firebase application ID.
    |
    */
    'app_id' => env('FIREBASE_APP_ID'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Measurement ID
    |--------------------------------------------------------------------------
    |
    | Your Google Analytics measurement ID (if using Analytics).
    |
    */
    'measurement_id' => env('FIREBASE_MEASUREMENT_ID'),
];
