<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use App\Models\LoginLog;
use Illuminate\Support\Facades\Log;

class LogFailedLogin
{
    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        try {
            LoginLog::create([
                'user_id' => $event->user ? $event->user->id : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'login_at' => now(),
                'status' => 'failed',
                'location_data' => [
                    'city' => 'Unknown',
                    'country' => 'Unknown'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log failed login: ' . $e->getMessage());
        }
    }
}
