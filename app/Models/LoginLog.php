<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoginLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'ip_address',
        'device_info',
        'location',
        'country'
    ];

    /**
     * Get the user associated with this login log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new login log entry
     *
     * @param array $data
     * @return static
     */
    public static function log(array $data)
    {
        return static::create([
            'user_id' => $data['user_id'],
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'device_info' => $data['device_info'] ?? request()->userAgent(),
            'location' => $data['location'] ?? null,
            'country' => $data['country'] ?? null
        ]);
    }
}
