<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\DriverController;
use App\Http\Controllers\Api\Admin\ZoneController;
use App\Http\Controllers\Api\Admin\LocationController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\ReportController;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for admin functionality. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Admin Authentication
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected Admin Routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Driver Management
    Route::apiResource('drivers', DriverController::class);

    // Zone Management
    Route::apiResource('zones', ZoneController::class);
    Route::get('/zones/{zone}/statistics', [ZoneController::class, 'statistics']);

    // Location Management
    Route::apiResource('locations', LocationController::class);

    // Settings
    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings', [SettingController::class, 'update']);
    Route::post('/settings/branding', [SettingController::class, 'updateBranding']);

    // Reports & Analytics
    Route::prefix('reports')->group(function () {
        Route::get('/activity', [ReportController::class, 'activity']);
        Route::get('/performance', [ReportController::class, 'performance']);
        Route::get('/export', [ReportController::class, 'export']);
    });
});
