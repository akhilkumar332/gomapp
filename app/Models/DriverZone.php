<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverZone extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'driver_id',
        'zone_id'
    ];

    /**
     * Get the driver that owns this assignment.
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id')
                    ->where('role', 'driver');
    }

    /**
     * Get the zone that this assignment belongs to.
     */
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
