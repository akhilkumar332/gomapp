<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'status',
        'priority',
        'started_at',
        'completed_at',
        'completed_by',
        'payment_received',
        'payment_amount_received'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'status' => 'string',
        'priority' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'payment_received' => 'boolean',
        'payment_amount_received' => 'decimal:2'
    ];

    /**
     * Get the zone that this location belongs to.
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Get the driver who completed this delivery.
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
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

    /**
     * Check if location delivery is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed' && !is_null($this->completed_at);
    }

    /**
     * Check if payment has been received
     */
    public function hasPayment(): bool
    {
        return $this->payment_received && $this->payment_amount_received > 0;
    }

    /**
     * Get delivery duration in minutes
     */
    public function getDeliveryDuration(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInMinutes($this->completed_at);
        }
        return null;
    }
}
