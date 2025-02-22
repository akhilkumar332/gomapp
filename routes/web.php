<?php

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
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    // Authentication Routes
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AuthController::class, 'dashboard'])->name('dashboard');

    // Zone Management
    Route::resource('zones', ZoneController::class);
    Route::post('zones/bulk-delete', [ZoneController::class, 'bulkDelete'])->name('zones.bulk-delete');
    Route::post('zones/{zone}/assign-drivers', [ZoneController::class, 'assignDrivers'])->name('zones.assign-drivers');

    // Location Management
    Route::resource('locations', LocationController::class);
    Route::post('locations/bulk-delete', [LocationController::class, 'bulkDelete'])->name('locations.bulk-delete');
    Route::post('locations/import', [LocationController::class, 'import'])->name('locations.import');
    Route::get('locations/export', [LocationController::class, 'export'])->name('locations.export');

    // Payment Management
    Route::resource('payments', PaymentController::class);
    Route::get('payments/export', [PaymentController::class, 'export'])->name('payments.export');

    // Activity Logs
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('activity-logs/{log}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    Route::get('activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');

    // Login Logs
    Route::get('login-logs', [LoginLogController::class, 'index'])->name('login-logs.index');
    Route::get('login-logs/{log}', [LoginLogController::class, 'show'])->name('login-logs.show');
    Route::get('login-logs/export', [LoginLogController::class, 'export'])->name('login-logs.export');

    // App Settings
    Route::get('settings', [AppSettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [AppSettingController::class, 'update'])->name('settings.update');
    Route::post('settings/branding', [AppSettingController::class, 'updateBranding'])->name('settings.branding');

    // Reports & Analytics
    Route::prefix('reports')->name('reports.')->group(function () {
        // Reports Dashboard
        Route::get('/', [ReportsController::class, 'index'])->name('index');

        // Driver Activity Reports
        Route::get('driver-activity', [ReportsController::class, 'driverActivity'])->name('driver-activity');
        Route::get('driver-activity/export', [ReportsController::class, 'exportDriverActivity'])->name('driver-activity.export');

        // Zone Statistics
        Route::get('zone-statistics', [ReportsController::class, 'zoneStatistics'])->name('zone-statistics');
        Route::get('zone-statistics/export', [ReportsController::class, 'exportZoneStatistics'])->name('zone-statistics.export');

        // System Usage Reports
        Route::get('system-usage', [ReportsController::class, 'systemUsage'])->name('system-usage');
        Route::get('system-usage/export', [ReportsController::class, 'exportSystemUsage'])->name('system-usage.export');
    });

    // Profile Management
    Route::get('profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');
});

// Driver Routes
Route::prefix('driver')->middleware(['auth', 'driver'])->name('driver.')->group(function () {
    Route::get('/', [AuthController::class, 'driverDashboard'])->name('dashboard');
    Route::get('profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');
});

// Shared Routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

// Fallback Route
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
