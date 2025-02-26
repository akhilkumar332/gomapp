<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Zone extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'boundaries',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string',
        'boundaries' => 'json'
    ];

    /**
     * Get all locations in this zone.
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    /**
     * Get active locations in this zone.
     */
    public function activeLocations(): HasMany
    {
        return $this->locations()->whereNull('completed_at');
    }

    /**
     * Get completed locations in this zone.
     */
    public function completedLocations(): HasMany
    {
        return $this->locations()->whereNotNull('completed_at');
    }

    /**
     * Get the drivers assigned to this zone.
     */
    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'driver_zones', 'zone_id', 'driver_id')
                    ->where('role', 'driver')
                    ->withTimestamps();
    }

    /**
     * Get active drivers in this zone.
     */
    public function activeDrivers(): BelongsToMany
    {
        return $this->drivers()->where('status', 'active');
    }

    /**
     * Check if zone is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the total number of locations in this zone
     */
    public function getLocationsCountAttribute(): int
    {
        return $this->locations()->count();
    }

    /**
     * Get the number of active locations in this zone
     */
    public function getActiveLocationsCountAttribute(): int
    {
        return $this->activeLocations()->count();
    }

    /**
     * Get the number of completed locations in this zone
     */
    public function getCompletedLocationsCountAttribute(): int
    {
        return $this->completedLocations()->count();
    }

    /**
     * Get the number of active drivers in this zone
     */
    public function getActiveDriversCountAttribute(): int
    {
        return $this->activeDrivers()->count();
    }

    /**
     * Get the completion rate for this zone
     */
    public function getCompletionRateAttribute(): float
    {
        $total = $this->locations_count;
        if ($total === 0) {
            return 0;
        }

        return ($this->completed_locations_count / $total) * 100;
    }
}
