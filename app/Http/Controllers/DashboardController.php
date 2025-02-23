<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\User;
use App\Models\Location;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        $oneHourAgo = Carbon::now()->subHour()->format('Y-m-d H:i:s');
        $activeDrivers = User::where('role', 'driver')
            ->whereNotNull('last_location_update')
            ->whereRaw("datetime(last_location_update) >= datetime(?)", [$oneHourAgo])
            ->count();

        // Get total locations
        $totalLocations = Location::count();

        // Get today's collections
        $today = Carbon::today()->format('Y-m-d');
        $todayCollections = Location::whereRaw("date(completed_at) = ?", [$today])
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
        $today = Carbon::today()->format('Y-m-d');
        $todayCollections = Location::where('completed_by', $driver->id)
            ->whereRaw("date(completed_at) = ?", [$today])
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
        $thirtyDaysAgo = Carbon::now()->subDays(30)->format('Y-m-d');
        
        $totalDeliveries = Location::whereNotNull('completed_at')
            ->whereRaw("date(completed_at) >= ?", [$thirtyDaysAgo])
            ->count();

        $successfulDeliveries = Location::where('status', 'completed')
            ->whereRaw("date(completed_at) >= ?", [$thirtyDaysAgo])
            ->count();

        return $totalDeliveries > 0 
            ? round(($successfulDeliveries / $totalDeliveries) * 100, 2)
            : 0;
    }

    /**
     * Calculate average delivery time in minutes using SQLite compatible functions
     */
    private function getAverageDeliveryTime()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30)->format('Y-m-d');
        
        return Location::where('status', 'completed')
            ->whereRaw("date(completed_at) >= ?", [$thirtyDaysAgo])
            ->whereNotNull('started_at')
            ->select(DB::raw('AVG(CAST((julianday(completed_at) - julianday(started_at)) * 24 * 60 AS INTEGER)) as avg_time'))
            ->value('avg_time') ?? 0;
    }

    /**
     * Get collections by zone
     */
    private function getCollectionsByZone()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30)->format('Y-m-d');
        
        return Zone::with(['locations' => function ($query) use ($thirtyDaysAgo) {
            $query->where('status', 'completed')
                ->where('payment_received', true)
                ->whereRaw("date(completed_at) >= ?", [$thirtyDaysAgo]);
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
        $thirtyDaysAgo = Carbon::now()->subDays(30)->format('Y-m-d');
        
        $drivers = User::where('role', 'driver')
            ->select('users.*')
            ->selectRaw('COUNT(DISTINCT locations.id) as completed_locations_count')
            ->selectRaw('SUM(CASE WHEN locations.payment_received = 1 THEN locations.payment_amount_received ELSE 0 END) as total_collections')
            ->leftJoin('locations', function ($join) use ($thirtyDaysAgo) {
                $join->on('users.id', '=', 'locations.completed_by')
                    ->whereRaw("date(locations.completed_at) >= ?", [$thirtyDaysAgo]);
            })
            ->groupBy('users.id')
            ->having('completed_locations_count', '>', 0)
            ->get();

        return $drivers->map(function ($driver) {
            return [
                'id' => $driver->id,
                'name' => $driver->name,
                'completed_deliveries' => $driver->completed_locations_count,
                'total_collections' => $driver->total_collections,
            ];
        });
    }
}
