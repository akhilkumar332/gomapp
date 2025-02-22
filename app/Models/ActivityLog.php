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
        'description',
        'device_type',
        'ip_address',
        'user_agent'
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
     * @param string $description
     * @param string|null $deviceType
     * @return static
     */
    public static function log(string $action, string $description, ?string $deviceType = null)
    {
        $request = request();
        
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'device_type' => $deviceType ?? 'web',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
    }

    /**
     * Get device type from user agent
     *
     * @param string|null $userAgent
     * @return string
     */
    public static function getDeviceType(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android') || str_contains($userAgent, 'iphone')) {
            return 'mobile';
        } elseif (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'tablet';
        }

        return 'desktop';
    }
}
