<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DriverController;

// Driver Dashboard
Route::get('/dashboard', [AuthController::class, 'driverDashboard'])->name('dashboard');

// Profile Management
Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
Route::post('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

// Delivery Management
Route::prefix('deliveries')->group(function () {
    Route::get('/', [DriverController::class, 'deliveries'])->name('deliveries.index');
    Route::get('/assigned', [DriverController::class, 'assignedDeliveries'])->name('deliveries.assigned');
    Route::get('/completed', [DriverController::class, 'completedDeliveries'])->name('deliveries.completed');
    Route::get('/{delivery}', [DriverController::class, 'showDelivery'])->name('deliveries.show');
    Route::post('/{delivery}/accept', [DriverController::class, 'acceptDelivery'])->name('deliveries.accept');
    Route::post('/{delivery}/complete', [DriverController::class, 'completeDelivery'])->name('deliveries.complete');
    Route::post('/{delivery}/update-status', [DriverController::class, 'updateDeliveryStatus'])->name('deliveries.update-status');
});

// Zone Management
Route::prefix('zones')->group(function () {
    Route::get('/', [DriverController::class, 'zones'])->name('zones.index');
    Route::get('/{zone}', [DriverController::class, 'showZone'])->name('zones.show');
    Route::post('/{zone}/accept', [DriverController::class, 'acceptZone'])->name('zones.accept');
});

// Location Management
Route::prefix('locations')->group(function () {
    Route::get('/', [DriverController::class, 'locations'])->name('locations.index');
    Route::get('/{location}', [DriverController::class, 'showLocation'])->name('locations.show');
    Route::post('/{location}/update-status', [DriverController::class, 'updateLocationStatus'])->name('locations.update-status');
});

// Reports
Route::prefix('reports')->group(function () {
    Route::get('/performance', [DriverController::class, 'performanceReport'])->name('reports.performance');
    Route::get('/earnings', [DriverController::class, 'earningsReport'])->name('reports.earnings');
    Route::get('/activity', [DriverController::class, 'activityReport'])->name('reports.activity');
});

// Settings
Route::prefix('settings')->group(function () {
    Route::get('/notifications', [DriverController::class, 'notificationSettings'])->name('settings.notifications');
    Route::post('/notifications', [DriverController::class, 'updateNotificationSettings'])->name('settings.notifications.update');
    Route::get('/availability', [DriverController::class, 'availabilitySettings'])->name('settings.availability');
    Route::post('/availability', [DriverController::class, 'updateAvailabilitySettings'])->name('settings.availability.update');
});

// Activity Logs
Route::get('/activity-logs', [DriverController::class, 'activityLogs'])->name('activity-logs.index');
