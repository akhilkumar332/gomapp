<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'device_type',
        'details'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'details' => 'array'
    ];

    /**
     * Get the user associated with this activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a new activity
     *
     * @param string $action
     * @param array $details
     * @param string|null $deviceType
     * @return static
     */
    public static function log(string $action, array $details = [], ?string $deviceType = null)
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'device_type' => $deviceType ?? 'web',
            'details' => $details
        ]);
    }
}
