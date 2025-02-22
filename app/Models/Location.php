<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'zone_id',
        'shop_name',
        'address',
        'ghana_post_gps_code',
        'latitude',
        'longitude',
        'contact_number',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'status' => 'string'
    ];

    /**
     * Get the zone that this location belongs to.
     */
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Get the payments associated with this location.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if location is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
