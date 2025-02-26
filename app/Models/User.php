<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'role',
        'status',
        'phone_verified',
        'phone_verified_at',
        'firebase_uid',
        'device_token',
        'last_latitude',
        'last_longitude',
        'last_location_update',
        'last_activity',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_location_update' => 'datetime',
        'last_activity' => 'datetime',
        'phone_verified' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Get the zones assigned to the driver.
     */
    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'driver_zones', 'driver_id', 'zone_id')
            ->withTimestamps();
    }

    /**
     * Get all locations assigned to the driver.
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'assigned_to');
    }

    /**
     * Get active locations assigned to the driver.
     */
    public function activeLocations(): HasMany
    {
        return $this->locations()->whereNull('completed_at');
    }

    /**
     * Get the completed locations for the driver.
     */
    public function completedLocations(): HasMany
    {
        return $this->hasMany(Location::class, 'completed_by')
            ->whereNotNull('completed_at');
    }

    /**
     * Get locations completed today.
     */
    public function todayCompletedLocations(): HasMany
    {
        return $this->completedLocations()
            ->whereDate('completed_at', today());
    }

    /**
     * Get locations completed this week.
     */
    public function weekCompletedLocations(): HasMany
    {
        return $this->completedLocations()
            ->where('completed_at', '>=', now()->startOfWeek());
    }

    /**
     * Get locations completed this month.
     */
    public function monthCompletedLocations(): HasMany
    {
        return $this->completedLocations()
            ->where('completed_at', '>=', now()->startOfMonth());
    }

    /**
     * Get the activity logs for the user.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a driver.
     */
    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }

    /**
     * Check if the user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the user is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if the user has verified their phone number.
     */
    public function hasVerifiedPhone(): bool
    {
        return $this->phone_verified && !is_null($this->phone_verified_at);
    }

    /**
     * Mark the user's phone number as verified.
     */
    public function markPhoneAsVerified(): bool
    {
        return $this->forceFill([
            'phone_verified' => true,
            'phone_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Check if the user is currently online.
     */
    public function isOnline(): bool
    {
        if (!$this->last_activity) {
            return false;
        }

        // Consider user online if they've been active in the last 5 minutes
        return $this->last_activity->diffInMinutes(now()) < 5;
    }

    /**
     * Get the total number of locations assigned to the driver
     */
    public function getLocationsCountAttribute(): int
    {
        return $this->locations()->count();
    }

    /**
     * Get the number of active locations assigned to the driver
     */
    public function getActiveLocationsCountAttribute(): int
    {
        return $this->activeLocations()->count();
    }

    /**
     * Get the number of completed locations for the driver
     */
    public function getCompletedLocationsCountAttribute(): int
    {
        return $this->completedLocations()->count();
    }

    /**
     * Get the completion rate for the driver
     */
    public function getCompletionRateAttribute(): float
    {
        $total = $this->locations_count;
        if ($total === 0) {
            return 0;
        }

        return ($this->completed_locations_count / $total) * 100;
    }

    /**
     * Get the on-time delivery rate for the driver
     */
    public function getOnTimeRateAttribute(): float
    {
        $completed = $this->completedLocations;
        if ($completed->isEmpty()) {
            return 0;
        }

        $onTime = $completed->filter(function ($location) {
            return $location->isOnTime();
        })->count();

        return ($onTime / $completed->count()) * 100;
    }

    /**
     * Get the formatted phone number
     */
    public function getFormattedPhoneAttribute(): string
    {
        if (empty($this->phone_number)) {
            return '-';
        }

        // Format: +233 XX XXX XXXX
        $phone = preg_replace('/[^0-9]/', '', $this->phone_number);
        if (strlen($phone) === 12) { // Including country code
            return '+' . substr($phone, 0, 3) . ' ' . 
                   substr($phone, 3, 2) . ' ' . 
                   substr($phone, 5, 3) . ' ' . 
                   substr($phone, 8);
        }

        return $this->phone_number;
    }
}
