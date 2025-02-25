<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Admin Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/activities', [DashboardController::class, 'getActivities'])->name('dashboard.activities');

// Profile
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
Route::put('/profile', [ProfileController::class, 'update']);

// Driver Management
Route::resource('drivers', DriverController::class);
Route::post('/drivers/{driver}/activate', [DriverController::class, 'activate'])->name('drivers.activate');
Route::post('/drivers/{driver}/deactivate', [DriverController::class, 'deactivate'])->name('drivers.deactivate');

// Zone Management
Route::resource('zones', ZoneController::class);
Route::get('/zones/{zone}/statistics', [ZoneController::class, 'statistics'])->name('zones.statistics');
Route::post('/zones/{zone}/assign-drivers', [ZoneController::class, 'assignDrivers'])->name('zones.assign-drivers');

// Location Management
Route::resource('locations', LocationController::class);
Route::post('/locations/{location}/assign-zone', [LocationController::class, 'assignZone'])->name('locations.assign-zone');
Route::post('/locations/import', [LocationController::class, 'import'])->name('locations.import');
Route::get('/locations/export', [LocationController::class, 'export'])->name('locations.export');

// Reports & Analytics
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/activity', [ReportController::class, 'activity'])->name('activity');
    Route::get('/performance', [ReportController::class, 'performance'])->name('performance');
    Route::get('/export', [ReportController::class, 'export'])->name('export');
    Route::post('/generate', [ReportController::class, 'generate'])->name('generate');
});

// Settings
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingController::class, 'index'])->name('index');
    Route::post('/', [SettingController::class, 'update'])->name('update');
    Route::post('/branding', [SettingController::class, 'updateBranding'])->name('branding');
});

// Activity Logs
Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
