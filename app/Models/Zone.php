<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string'
    ];

    /**
     * Get the locations in this zone.
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    /**
     * Get the drivers assigned to this zone.
     */
    public function drivers()
    {
        return $this->belongsToMany(User::class, 'driver_zones', 'zone_id', 'driver_id')
                    ->where('role', 'driver');
    }

    /**
     * Check if zone is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
