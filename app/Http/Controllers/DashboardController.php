<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Zone;
use App\Models\Location;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Basic Statistics
        $totalZones = Zone::count();
        $activeDrivers = User::where('role', 'driver')
            ->where('status', 'active')
            ->whereNotNull('last_location_update')
            ->where('last_location_update', '>=', now()->subHour())
            ->count();
        $totalLocations = Location::count();
        $todayCollections = Location::whereDate('completed_at', today())
            ->where('payment_received', true)
            ->sum('payment_amount_received');

        // Performance Metrics
        $performanceMetrics = $this->getPerformanceMetrics();

        // Delivery Chart Data
        $deliveryChart = $this->getDeliveryChartData();

        // Recent Activities
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalZones',
            'activeDrivers',
            'totalLocations',
            'todayCollections',
            'performanceMetrics',
            'deliveryChart',
            'recentActivities'
        ));
    }

    private function getPerformanceMetrics()
    {
        $thirtyDaysAgo = now()->subDays(30);

        $totalDeliveries = Location::where('status', 'completed')
            ->where('completed_at', '>=', $thirtyDaysAgo)
            ->count();

        $successfulDeliveries = Location::where('status', 'completed')
            ->where('completed_at', '>=', $thirtyDaysAgo)
            ->where('payment_received', true)
            ->count();

        $deliverySuccessRate = $totalDeliveries > 0 
            ? round(($successfulDeliveries / $totalDeliveries) * 100) 
            : 0;

        $averageDeliveryTime = Location::where('status', 'completed')
            ->where('completed_at', '>=', $thirtyDaysAgo)
            ->whereNotNull('started_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_time'))
            ->first()
            ->avg_time ?? 0;

        return [
            'delivery_success_rate' => $deliverySuccessRate,
            'average_delivery_time' => round($averageDeliveryTime)
        ];
    }

    private function getDeliveryChartData()
    {
        $days = 7;
        $labels = [];
        $completedData = [];
        $totalData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('D');

            $completed = Location::where('status', 'completed')
                ->whereDate('completed_at', $date)
                ->count();
            $completedData[] = $completed;

            $total = Location::whereDate('created_at', $date)->count();
            $totalData[] = $total;
        }

        return [
            'labels' => $labels,
            'completed' => $completedData,
            'total' => $totalData
        ];
    }

    public function driverDashboard()
    {
        $driver = auth()->user();
        
        // Get assigned zones
        $zones = $driver->zones()->with(['locations' => function ($query) {
            $query->where('status', 'active');
        }])->get();

        // Get today's completed deliveries
        $completedToday = Location::where('completed_by', $driver->id)
            ->whereDate('completed_at', today())
            ->count();

        // Get today's collections
        $todayCollections = Location::where('completed_by', $driver->id)
            ->whereDate('completed_at', today())
            ->where('payment_received', true)
            ->sum('payment_amount_received');

        // Get recent activities
        $recentActivities = ActivityLog::where('user_id', $driver->id)
            ->latest()
            ->take(10)
            ->get();

        return view('driver.dashboard', compact(
            'zones',
            'completedToday',
            'todayCollections',
            'recentActivities'
        ));
    }
}
