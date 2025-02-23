<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\LoginLog;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        try {
            LoginLog::create([
                'user_id' => $event->user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'login_at' => now(),
                'status' => 'success',
                'location_data' => [
                    'city' => 'Unknown',
                    'country' => 'Unknown'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log successful login: ' . $e->getMessage());
        }
    }
}
