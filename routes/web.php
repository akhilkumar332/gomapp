<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\DriverMiddleware;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
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
});

// Driver Routes
Route::middleware(['auth', DriverMiddleware::class])->prefix('driver')->name('driver.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'driverDashboard'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update']);
    
    // Zones
    Route::get('/zones', [ZoneController::class, 'driverIndex'])->name('zones.index');
    Route::get('/zones/{zone}', [ZoneController::class, 'driverShow'])->name('zones.show');
    Route::get('/zones/{zone}/locations', [ZoneController::class, 'driverLocations'])->name('zones.locations');
    
    // Locations
    Route::get('/locations', [LocationController::class, 'driverIndex'])->name('locations.index');
    Route::get('/locations/{location}', [LocationController::class, 'driverShow'])->name('locations.show');
    Route::post('/locations/{location}/status', [LocationController::class, 'updateStatus'])->name('locations.status');
    Route::post('/locations/{location}/position', [LocationController::class, 'updatePosition'])->name('locations.position');
    
    // Activities
    Route::get('/activities', [ActivityLogController::class, 'driverActivities'])->name('activities');
});
