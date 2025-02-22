<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DriverController;
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
});

// Authentication Routes
Route::controller(AuthController::class)->group(function () {
    Route::get('login', 'showLoginForm')->name('login');
    Route::post('login', 'login');
    Route::post('logout', 'logout')->name('logout')->middleware('auth');
});

// Admin Routes
Route::middleware(['web', 'auth', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
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

    // Driver Management
    Route::resource('drivers', DriverController::class);
    Route::post('drivers/bulk-delete', [DriverController::class, 'bulkDelete'])->name('drivers.bulk-delete');
    Route::post('drivers/import', [DriverController::class, 'import'])->name('drivers.import');
    Route::get('drivers/export', [DriverController::class, 'export'])->name('drivers.export');

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
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('driver-activity', [ReportsController::class, 'driverActivity'])->name('driver-activity');
        Route::get('driver-activity/export', [ReportsController::class, 'exportDriverActivity'])->name('driver-activity.export');
        Route::get('zone-statistics', [ReportsController::class, 'zoneStatistics'])->name('zone-statistics');
        Route::get('zone-statistics/export', [ReportsController::class, 'exportZoneStatistics'])->name('zone-statistics.export');
        Route::get('system-usage', [ReportsController::class, 'systemUsage'])->name('system-usage');
        Route::get('system-usage/export', [ReportsController::class, 'exportSystemUsage'])->name('system-usage.export');
    });

    // Profile Management
    Route::get('profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');
});

// Driver Routes
Route::prefix('driver')->middleware(['auth', \App\Http\Middleware\DriverMiddleware::class])->name('driver.')->group(function () {
    Route::get('/', [AuthController::class, 'driverDashboard'])->name('dashboard');
    Route::get('profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');
});

// Fallback Route
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
