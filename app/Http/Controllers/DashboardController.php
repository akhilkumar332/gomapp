<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\User;
use App\Models\Location;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function index()
    {
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
            ->sum('payment_amount_received');

        // Get recent activities
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Get performance metrics
        $performanceMetrics = [
            'delivery_success_rate' => $this->getDeliverySuccessRate(),
            'average_delivery_time' => $this->getAverageDeliveryTime(),
            'collections_by_zone' => $this->getCollectionsByZone(),
            'driver_performance' => $this->getDriverPerformance(),
        ];

        return view('admin.dashboard', compact(
            'totalZones',
            'activeDrivers',
            'totalLocations',
            'todayCollections',
            'recentActivities',
            'performanceMetrics'
        ));
    }

    /**
     * Show the driver dashboard.
     */
    public function driverDashboard(Request $request)
    {
        $driver = $request->user();

        // Get assigned zones
        $zones = $driver->zones()
            ->with(['locations' => function ($query) {
                $query->whereNull('completed_at')
                    ->orderBy('priority', 'desc');
            }])
            ->get();

        // Get today's collections
        $todayCollections = Location::where('completed_by', $driver->id)
            ->whereDate('completed_at', today())
            ->where('status', 'completed')
            ->where('payment_received', true)
            ->sum('payment_amount_received');

        // Get recent activities
        $recentActivities = ActivityLog::where('user_id', $driver->id)
            ->latest()
            ->take(10)
            ->get();

        // Get performance metrics
        $performanceMetrics = [
            'total_deliveries' => Location::where('completed_by', $driver->id)->count(),
            'successful_deliveries' => Location::where('completed_by', $driver->id)
                ->where('status', 'completed')
                ->count(),
            'total_collections' => Location::where('completed_by', $driver->id)
                ->where('payment_received', true)
                ->sum('payment_amount_received'),
        ];

        return view('driver.dashboard', compact(
            'zones',
            'todayCollections',
            'recentActivities',
            'performanceMetrics'
        ));
    }

    /**
     * Calculate delivery success rate
     */
    private function getDeliverySuccessRate()
    {
        $totalDeliveries = Location::whereNotNull('completed_at')
            ->whereDate('completed_at', '>=', now()->subDays(30))
            ->count();

        $successfulDeliveries = Location::where('status', 'completed')
            ->whereDate('completed_at', '>=', now()->subDays(30))
            ->count();

        return $totalDeliveries > 0 
            ? round(($successfulDeliveries / $totalDeliveries) * 100, 2)
            : 0;
    }

    /**
     * Calculate average delivery time
     */
    private function getAverageDeliveryTime()
    {
        return Location::where('status', 'completed')
            ->whereDate('completed_at', '>=', now()->subDays(30))
            ->whereNotNull('started_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_time'))
            ->first()
            ->avg_time ?? 0;
    }

    /**
     * Get collections by zone
     */
    private function getCollectionsByZone()
    {
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
    }

    /**
     * Get driver performance metrics
     */
    private function getDriverPerformance()
    {
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
                    'total_collections' => $driver->completed_locations_sum_payment_amount_received,
                ];
            });
    }
}
