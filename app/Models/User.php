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
        'phone_verified' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Get the zones assigned to the user.
     */
    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'driver_zones', 'driver_id', 'zone_id')
            ->withTimestamps();
    }

    /**
     * Get the completed locations for the user.
     */
    public function completedLocations(): HasMany
    {
        return $this->hasMany(Location::class, 'completed_by')
            ->whereNotNull('completed_at');
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
}
