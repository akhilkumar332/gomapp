<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\LoginLogController;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\ReportsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::prefix('auth')->group(function () {
    // Admin Authentication
    Route::post('/admin/login', [AuthController::class, 'adminLogin']);
    Route::post('/admin/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // Driver Authentication with Firebase Phone
    Route::post('/driver/login', [AuthController::class, 'driverLogin']);
    Route::post('/driver/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/driver/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // User Profile
    Route::get('user/profile', [AuthController::class, 'profile']);
    Route::post('user/profile', [AuthController::class, 'updateProfile']);

    // Admin Only Routes
    Route::middleware('admin')->group(function () {
        // Zone Management
        Route::apiResource('zones', ZoneController::class);
        
        // Location Management (Admin)
        Route::apiResource('locations', LocationController::class);
        
        // App Settings
        Route::get('app-settings', [AppSettingController::class, 'index']);
        Route::post('app-settings', [AppSettingController::class, 'update']);
        Route::get('app-settings/{key}', [AppSettingController::class, 'show']);
        Route::delete('app-settings/{key}', [AppSettingController::class, 'destroy']);
        Route::post('app-settings/branding', [AppSettingController::class, 'updateBranding']);
        
        // Activity Logs
        Route::get('activity-logs', [ActivityLogController::class, 'index']);
        Route::get('activity-logs/{log}', [ActivityLogController::class, 'show']);
        Route::get('activity-logs/statistics', [ActivityLogController::class, 'getStatistics']);
        Route::get('activity-logs/export', [ActivityLogController::class, 'export']);
        
        // Login Logs
        Route::get('login-logs', [LoginLogController::class, 'index']);
        Route::get('login-logs/{log}', [LoginLogController::class, 'show']);
        Route::get('login-logs/export', [LoginLogController::class, 'export']);

        // Reports & Analytics
        Route::prefix('reports')->group(function () {
            // Driver Activity Reports
            Route::get('driver-activity', [ReportsController::class, 'driverActivity']);
            Route::get('driver-activity/export', [ReportsController::class, 'exportDriverActivity']);
            
            // Zone Statistics
            Route::get('zone-statistics', [ReportsController::class, 'zoneStatistics']);
            Route::get('zone-statistics/export', [ReportsController::class, 'exportZoneStatistics']);
            
            // System Usage Reports
            Route::get('system-usage', [ReportsController::class, 'systemUsage']);
            Route::get('system-usage/export', [ReportsController::class, 'exportSystemUsage']);
        });
    });

    // Driver Only Routes
    Route::middleware('driver')->group(function () {
        // Zone Access
        Route::get('driver/zones', [ZoneController::class, 'getAssignedZones']);
        
        // Location Management (Driver)
        Route::get('driver/locations/{zone}', [LocationController::class, 'getZoneLocations']);
        Route::post('driver/locations', [LocationController::class, 'store']);
        
        // Payment Management
        Route::get('driver/payments', [PaymentController::class, 'index']);
        Route::post('driver/payments', [PaymentController::class, 'store']);
        Route::get('driver/payments/{payment}', [PaymentController::class, 'show']);
        
        // Activity Logs (Driver's own logs)
        Route::get('driver/activity-logs', [ActivityLogController::class, 'index']);
        
        // Login Logs (Driver's own logs)
        Route::get('driver/login-logs', [LoginLogController::class, 'index']);

        // Driver Reports
        Route::get('driver/reports/activity', [ReportsController::class, 'driverPersonalActivity']);
        Route::get('driver/reports/statistics', [ReportsController::class, 'driverPersonalStatistics']);
    });

    // Shared Routes (Available to both Admin and Driver)
    Route::get('app-settings/branding', [AppSettingController::class, 'getBranding']);
});

// Public Routes
Route::get('health', function () {
    return response()->json(['status' => 'healthy']);
});
