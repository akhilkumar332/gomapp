<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\ActivityLog;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Get recent activities for AJAX refresh
     */
    public function activities()
    {
        try {
            $activities = ActivityLog::with('user')
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($activity) {
                    return [
                        'time' => $activity->created_at->diffForHumans(),
                        'user' => $activity->user ? $activity->user->name : 'System',
                        'description' => $activity->description,
                        'status' => $activity->status,
                        'status_color' => match($activity->status) {
                            'success' => 'success',
                            'warning' => 'warning',
                            'error' => 'danger',
                            default => 'secondary',
                        },
                    ];
                });

            return response()->json([
                'success' => true,
                'activities' => $activities
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching dashboard activities: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching activities',
                'activities' => []
            ], 500);
        }
    }
}
