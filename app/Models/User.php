<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
        'firebase_uid',
        'phone_verified',
        'phone_verified_at',
        'device_token',
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
        'phone_verified' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Get the zones assigned to the driver.
     */
    public function zones()
    {
        return $this->belongsToMany(Zone::class, 'driver_zones')
            ->withTimestamps();
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is driver
     */
    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }

    /**
     * Check if phone is verified
     */
    public function hasVerifiedPhone(): bool
    {
        return $this->phone_verified;
    }

    /**
     * Mark phone as verified
     */
    public function markPhoneAsVerified(): bool
    {
        if (!$this->phone_verified) {
            $this->phone_verified = true;
            $this->phone_verified_at = $this->freshTimestamp();
            return $this->save();
        }
        return true;
    }

    /**
     * Update device token
     */
    public function updateDeviceToken(?string $token): bool
    {
        $this->device_token = $token;
        return $this->save();
    }
}
