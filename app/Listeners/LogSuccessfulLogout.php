<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Models\LoginLog;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        try {
            if ($event->user) {
                $lastLogin = LoginLog::where('user_id', $event->user->id)
                    ->where('status', 'success')
                    ->latest()
                    ->first();

                if ($lastLogin) {
                    $lastLogin->update([
                        'logout_at' => now()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to log successful logout: ' . $e->getMessage());
        }
    }
}
