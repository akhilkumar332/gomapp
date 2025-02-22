<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'role'
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
        'password' => 'hashed',
        'role' => 'string'
    ];

    /**
     * Get the zones assigned to this driver.
     */
    public function zones()
    {
        return $this->belongsToMany(Zone::class, 'driver_zones', 'driver_id', 'zone_id')
                    ->when($this->isDriver(), function ($query) {
                        return $query->where('status', 'active');
                    });
    }

    /**
     * Get the login logs for this user.
     */
    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class);
    }

    /**
     * Get the activity logs for this user.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a driver
     */
    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }
}
