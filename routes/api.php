<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ZoneController;
use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\DriverController;
use App\Http\Controllers\Api\Admin\ZoneController as AdminZoneController;
use App\Http\Controllers\Api\Admin\LocationController as AdminLocationController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes
Route::post('/auth/verify-phone', [AuthController::class, 'verifyPhone']);

// Protected Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // User Profile
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/user/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Driver Routes (requires phone verification)
    Route::middleware(['verified.phone', 'driver'])->group(function () {
        // Zones
        Route::get('/zones', [ZoneController::class, 'index']);
        Route::get('/zones/{zone}', [ZoneController::class, 'show']);
        Route::get('/zones/{zone}/locations', [ZoneController::class, 'locations']);

        // Locations
        Route::get('/locations', [LocationController::class, 'index']);
        Route::get('/locations/{location}', [LocationController::class, 'show']);
        Route::post('/locations/{location}/status', [LocationController::class, 'updateStatus']);
        Route::post('/locations/{location}/position', [LocationController::class, 'updatePosition']);
    });
});

// Admin Authentication
Route::post('/admin/auth/login', [AdminAuthController::class, 'login']);

// Protected Admin Routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Auth
    Route::post('/admin/auth/logout', [AdminAuthController::class, 'logout']);

    // Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'index']);

    // Driver Management
    Route::apiResource('admin/drivers', DriverController::class);

    // Zone Management
    Route::apiResource('admin/zones', AdminZoneController::class);
    Route::get('/admin/zones/{zone}/statistics', [AdminZoneController::class, 'statistics']);

    // Location Management
    Route::apiResource('admin/locations', AdminLocationController::class);

    // Settings
    Route::get('/admin/settings', [SettingController::class, 'index']);
    Route::post('/admin/settings', [SettingController::class, 'update']);
    Route::post('/admin/settings/branding', [SettingController::class, 'updateBranding']);

    // Reports & Analytics
    Route::prefix('admin/reports')->group(function () {
        Route::get('/activity', [ReportController::class, 'activity']);
        Route::get('/performance', [ReportController::class, 'performance']);
        Route::get('/export', [ReportController::class, 'export']);
    });
});
