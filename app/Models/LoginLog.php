<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'login_at',
        'location_data'
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'location_data' => 'array'
    ];

    /**
     * Get the user that owns the login log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
