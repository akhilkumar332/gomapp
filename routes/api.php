<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\DriverController;
use App\Http\Controllers\Api\Admin\ZoneController;
use App\Http\Controllers\Api\Admin\LocationController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\Api\Driver\ZoneController as DriverZoneController;
use App\Http\Controllers\Api\Driver\LocationController as DriverLocationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health Check Routes
Route::prefix('health-check')->middleware(['throttle:health-check'])->group(function () {
    Route::get('/', [HealthCheckController::class, 'index'])
        ->name('health-check.index');
    
    Route::get('/database', [HealthCheckController::class, 'database'])
        ->name('health-check.database');
    
    Route::get('/network', [HealthCheckController::class, 'network'])
        ->name('health-check.network');
    
    Route::get('/metrics', [HealthCheckController::class, 'metrics'])
        ->middleware(['auth:sanctum', 'ability:admin'])
        ->name('health-check.metrics');
});

// Public Routes
Route::post('/auth/verify-phone', [AuthController::class, 'verifyPhone']);

// Admin API Routes
Route::prefix('admin')->group(function () {
    // Admin Authentication
    Route::post('/auth/login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:auth');

    // Protected Admin Routes
    Route::middleware(['auth:sanctum', 'ability:admin', 'throttle:admin-api'])->group(function () {
        // Auth
        Route::post('/auth/logout', [AdminAuthController::class, 'logout']);
        Route::post('/auth/refresh', [AdminAuthController::class, 'refresh']);

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/statistics', [DashboardController::class, 'statistics']);
        Route::get('/dashboard/performance', [DashboardController::class, 'performance']);

        // Driver Management
        Route::apiResource('drivers', DriverController::class);
        Route::post('/drivers/{driver}/activate', [DriverController::class, 'activate']);
        Route::post('/drivers/{driver}/deactivate', [DriverController::class, 'deactivate']);
        Route::get('/drivers/{driver}/performance', [DriverController::class, 'performance']);
        Route::get('/drivers/{driver}/activities', [DriverController::class, 'activities']);

        // Zone Management
        Route::apiResource('zones', ZoneController::class);
        Route::get('/zones/{zone}/statistics', [ZoneController::class, 'statistics']);
        Route::post('/zones/{zone}/assign-drivers', [ZoneController::class, 'assignDrivers']);
        Route::get('/zones/{zone}/drivers', [ZoneController::class, 'drivers']);
        Route::get('/zones/{zone}/locations', [ZoneController::class, 'locations']);

        // Location Management
        Route::apiResource('locations', LocationController::class);
        Route::post('/locations/import', [LocationController::class, 'import']);
        Route::get('/locations/export', [LocationController::class, 'export']);
        Route::post('/locations/{location}/assign-zone', [LocationController::class, 'assignZone']);
        Route::get('/locations/{location}/history', [LocationController::class, 'history']);

        // Settings
        Route::get('/settings', [SettingController::class, 'index']);
        Route::post('/settings', [SettingController::class, 'update']);
        Route::post('/settings/branding', [SettingController::class, 'updateBranding']);
        Route::get('/settings/notifications', [SettingController::class, 'notifications']);
        Route::post('/settings/notifications', [SettingController::class, 'updateNotifications']);

        // Reports & Analytics
        Route::prefix('reports')->group(function () {
            Route::get('/activity', [ReportController::class, 'activity']);
            Route::get('/performance', [ReportController::class, 'performance']);
            Route::get('/collections', [ReportController::class, 'collections']);
            Route::get('/zones', [ReportController::class, 'zoneAnalytics']);
            Route::get('/drivers', [ReportController::class, 'driverAnalytics']);
            Route::post('/generate', [ReportController::class, 'generate']);
            Route::get('/export', [ReportController::class, 'export']);
        });
    });
});

// Driver API Routes
Route::middleware(['auth:sanctum', 'ability:driver', 'throttle:driver-api'])->prefix('driver')->group(function () {
    // Profile
    Route::get('/profile', [DriverController::class, 'profile']);
    Route::post('/profile', [DriverController::class, 'updateProfile']);
    Route::post('/location', [DriverController::class, 'updateLocation']);
    Route::post('/device-token', [DriverController::class, 'updateDeviceToken']);

    // Zones
    Route::get('/zones', [DriverZoneController::class, 'index']);
    Route::get('/zones/{zone}', [DriverZoneController::class, 'show']);
    Route::get('/zones/{zone}/locations', [DriverZoneController::class, 'locations']);

    // Locations
    Route::get('/locations', [DriverLocationController::class, 'index']);
    Route::get('/locations/active', [DriverLocationController::class, 'active']);
    Route::get('/locations/completed', [DriverLocationController::class, 'completed']);
    Route::get('/locations/{location}', [DriverLocationController::class, 'show']);
    Route::post('/locations/{location}/start', [DriverLocationController::class, 'start']);
    Route::post('/locations/{location}/complete', [DriverLocationController::class, 'complete']);
    Route::post('/locations/{location}/cancel', [DriverLocationController::class, 'cancel']);
    Route::post('/locations/{location}/position', [DriverLocationController::class, 'updatePosition']);
    Route::post('/locations/{location}/payment', [DriverLocationController::class, 'recordPayment']);

    // Activities
    Route::get('/activities', [DriverController::class, 'activities']);
    Route::get('/statistics', [DriverController::class, 'statistics']);
});

// Webhook Routes
Route::prefix('webhooks')->group(function () {
    // Firebase Webhooks
    Route::post('/firebase', [WebhookController::class, 'handleFirebase'])
        ->middleware(['throttle:webhooks', 'verify.firebase.webhook'])
        ->name('webhooks.firebase');

    // Stripe Webhooks
    Route::post('/stripe', [WebhookController::class, 'handleStripe'])
        ->middleware(['throttle:webhooks', 'verify.stripe.webhook'])
        ->name('webhooks.stripe');

    // Twilio Webhooks
    Route::post('/twilio', [WebhookController::class, 'handleTwilio'])
        ->middleware(['throttle:webhooks', 'verify.twilio.webhook'])
        ->name('webhooks.twilio');
});

// Fallback route for undefined API endpoints
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found.'
    ], 404);
});
