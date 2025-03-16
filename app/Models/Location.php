<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'assigned_to',
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
     * Get the driver assigned to this location.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
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
    public function payments(): HasMany
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
     * Get the total number of completed deliveries for this location.
     */
    public function getTotalDeliveriesCount(): int
    {
        return $this->whereNotNull('completed_at')->count();
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

    /**
     * Check if delivery is on time (within 2 hours)
     */
    public function isOnTime(): bool
    {
        if (!$this->completed_at) {
            return false;
        }

        $start = $this->created_at;
        $end = $this->completed_at;
        return $end->diffInMinutes($start) <= 120; // 2 hours threshold
    }

    /**
     * Scope a query to only include active locations.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('completed_at');
    }

    /**
     * Scope a query to only include completed locations.
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * Scope a query to only include locations with payment received.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_received', true);
    }

    /**
     * Scope a query to only include locations with pending payment.
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_received', false);
    }

    /**
     * Scope a query to only include locations assigned to a specific driver.
     */
    public function scopeAssignedTo($query, $driverId)
    {
        return $query->where('assigned_to', $driverId);
    }

    /**
     * Scope a query to only include locations completed by a specific driver.
     */
    public function scopeCompletedBy($query, $driverId)
    {
        return $query->where('completed_by', $driverId);
    }

    /**
     * Get the formatted payment amount
     */
    public function getFormattedPaymentAmountAttribute(): string
    {
        return '₵' . number_format($this->payment_amount_received, 2);
    }

    /**
     * Get the formatted delivery duration
     */
    public function getFormattedDeliveryDurationAttribute(): string
    {
        $duration = $this->getDeliveryDuration();
        if (is_null($duration)) {
            return '-';
        }

        $hours = floor($duration / 60);
        $minutes = $duration % 60;
        return sprintf('%dh %dm', $hours, $minutes);
    }
}
