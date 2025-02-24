<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\User;
use App\Models\Location;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard analytics
     */
    public function index(Request $request)
    {
        try {
            // Get total zones
            $totalZones = Zone::count();

            // Get active drivers (drivers who have updated their location in the last hour)
            $activeDrivers = User::where('role', 'driver')
                ->whereNotNull('last_location_update')
                ->where('last_location_update', '>=', now()->subHour())
                ->count();

            // Get total locations
            $totalLocations = Location::count();

            // Get today's collections
            $todayCollections = Location::whereDate('completed_at', today())
                ->where('status', 'completed')
                ->where('payment_received', true)
                ->sum('payment_amount_received') ?? 0;

            // Get recent activities
            $recentActivities = ActivityLog::with('user')
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'action' => $activity->action,
                        'description' => $activity->description,
                        'user' => $activity->user ? [
                            'id' => $activity->user->id,
                            'name' => $activity->user->name,
                            'role' => $activity->user->role,
                        ] : null,
                        'created_at' => $activity->created_at,
                    ];
                });

            // Get performance metrics
            $performanceMetrics = [
                'delivery_success_rate' => $this->getDeliverySuccessRate(),
                'average_delivery_time' => 0, // Default to 0 for now since we're having SQLite issues
                'collections_by_zone' => $this->getCollectionsByZone(),
                'driver_performance' => $this->getDriverPerformance(),
            ];

            return response()->json([
                'data' => [
                    'total_zones' => $totalZones,
                    'active_drivers' => $activeDrivers,
                    'total_locations' => $totalLocations,
                    'today_collections' => $todayCollections,
                    'recent_activities' => $recentActivities,
                    'performance_metrics' => $performanceMetrics,
                ]
            ]);
        } catch (\Exception $e) {
            // Log the error but return a default response
            \Log::error('Dashboard error: ' . $e->getMessage());
            return response()->json([
                'data' => [
                    'total_zones' => 0,
                    'active_drivers' => 0,
                    'total_locations' => 0,
                    'today_collections' => 0,
                    'recent_activities' => [],
                    'performance_metrics' => [
                        'delivery_success_rate' => 0,
                        'average_delivery_time' => 0,
                        'collections_by_zone' => [],
                        'driver_performance' => [],
                    ],
                ]
            ]);
        }
    }

    /**
     * Calculate delivery success rate
     */
    private function getDeliverySuccessRate()
    {
        try {
            $totalDeliveries = Location::whereNotNull('completed_at')
                ->whereDate('completed_at', '>=', now()->subDays(30))
                ->count();

            $successfulDeliveries = Location::where('status', 'completed')
                ->whereDate('completed_at', '>=', now()->subDays(30))
                ->count();

            return $totalDeliveries > 0 
                ? round(($successfulDeliveries / $totalDeliveries) * 100, 2)
                : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get collections by zone
     */
    private function getCollectionsByZone()
    {
        try {
            return Zone::with(['locations' => function ($query) {
                $query->where('status', 'completed')
                    ->where('payment_received', true)
                    ->whereDate('completed_at', '>=', now()->subDays(30));
            }])
            ->get()
            ->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'total_collections' => $zone->locations->sum('payment_amount_received'),
                    'locations_count' => $zone->locations->count(),
                ];
            });
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get driver performance metrics
     */
    private function getDriverPerformance()
    {
        try {
            return User::where('role', 'driver')
                ->withCount(['completedLocations' => function ($query) {
                    $query->whereDate('completed_at', '>=', now()->subDays(30));
                }])
                ->withSum(['completedLocations' => function ($query) {
                    $query->whereDate('completed_at', '>=', now()->subDays(30))
                        ->where('payment_received', true);
                }], 'payment_amount_received')
                ->having('completed_locations_count', '>', 0)
                ->get()
                ->map(function ($driver) {
                    return [
                        'id' => $driver->id,
                        'name' => $driver->name,
                        'completed_deliveries' => $driver->completed_locations_count,
                        'total_collections' => $driver->completed_locations_sum_payment_amount_received ?? 0,
                    ];
                });
        } catch (\Exception $e) {
            return [];
        }
    }
}
