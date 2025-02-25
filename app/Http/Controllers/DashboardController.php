<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\User;
use App\Models\Location;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Get dashboard analytics with enhanced metrics and caching
     */
    public function index(Request $request)
    {
        try {
            // Cache key with hourly expiration for basic metrics
            $cacheKey = 'dashboard_metrics_' . date('Y-m-d_H');
            
            // Basic metrics with caching
            $basicMetrics = Cache::remember($cacheKey, 3600, function () {
                return [
                    'totalZones' => Zone::count(),
                    'activeDrivers' => User::where('role', 'driver')
                        ->whereNotNull('last_location_update')
                        ->where('last_location_update', '>=', now()->subHour())
                        ->count(),
                    'totalLocations' => Location::count(),
                    'todayCollections' => Location::whereDate('completed_at', today())
                        ->where('status', 'completed')
                        ->where('payment_received', true)
                        ->sum('payment_amount_received') ?? 0,
                ];
            });

            // Get recent activities
            $recentActivities = ActivityLog::with('user')
                ->latest()
                ->take(10)
                ->get();

            // Enhanced performance metrics
            $performanceMetrics = [
                'delivery_success_rate' => $this->getDeliverySuccessRate(),
                'average_delivery_time' => $this->getAverageDeliveryTime(),
                'collections_by_zone' => $this->getCollectionsByZone(),
                'driver_performance' => $this->getDriverPerformance(),
                'weekly_trends' => $this->getWeeklyTrends(),
            ];

            // Enhanced delivery chart data with weekly comparison
            $deliveryChart = $this->getDeliveryChartData();

            // Combine all data
            $data = array_merge($basicMetrics, [
                'recentActivities' => $recentActivities,
                'performanceMetrics' => $performanceMetrics,
                'deliveryChart' => $deliveryChart,
            ]);

            return view('admin.dashboard_fixed', $data);
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return fallback data
            return view('admin.dashboard', $this->getFallbackData());
        }
    }

    /**
     * Calculate average delivery time
     */
    private function getAverageDeliveryTime()
    {
        try {
            return Location::whereNotNull('started_at')
                ->whereNotNull('completed_at')
                ->where('status', 'completed')
                ->whereDate('completed_at', '>=', now()->subDays(30))
                ->get()
                ->avg(function ($location) {
                    return $location->started_at->diffInMinutes($location->completed_at);
                }) ?? 0;
        } catch (\Exception $e) {
            Log::error('Error calculating average delivery time: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get weekly delivery trends
     */
    private function getWeeklyTrends()
    {
        try {
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();

            return Location::select(
                DB::raw('DATE(completed_at) as date'),
                DB::raw('COUNT(*) as total_deliveries'),
                DB::raw('SUM(CASE WHEN status = "completed" AND payment_received = true THEN payment_amount_received ELSE 0 END) as total_collections')
            )
            ->whereBetween('completed_at', [$startOfWeek, $endOfWeek])
            ->groupBy('date')
            ->get()
            ->map(function ($record) {
                return [
                    'date' => $record->date,
                    'deliveries' => $record->total_deliveries,
                    'collections' => $record->total_collections,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error calculating weekly trends: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get enhanced delivery chart data
     */
    private function getDeliveryChartData()
    {
        try {
            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $completed = array_fill(0, 7, 0);
            $total = array_fill(0, 7, 0);
            $collections = array_fill(0, 7, 0);

            // Get data for the current week
            // Get data for each day of the week
            $startOfWeek = now()->startOfWeek();
            for ($i = 0; $i < 7; $i++) {
                $date = $startOfWeek->copy()->addDays($i);
                $dayData = Location::whereDate('completed_at', $date)
                    ->selectRaw('COUNT(*) as total')
                    ->selectRaw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
                    ->selectRaw('SUM(CASE WHEN payment_received = true THEN payment_amount_received ELSE 0 END) as collections')
                    ->first();

                $completed[$i] = $dayData->completed ?? 0;
                $total[$i] = $dayData->total ?? 0;
                $collections[$i] = $dayData->collections ?? 0;
            }

            return [
                'labels' => $days,
                'completed' => $completed,
                'total' => $total,
                'collections' => $collections,
            ];
        } catch (\Exception $e) {
            Log::error('Error generating delivery chart data: ' . $e->getMessage());
            return [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'completed' => array_fill(0, 7, 0),
                'total' => array_fill(0, 7, 0),
                'collections' => array_fill(0, 7, 0),
            ];
        }
    }

    /**
     * Get fallback data in case of errors
     */
    private function getFallbackData()
    {
        return [
            'totalZones' => 0,
            'activeDrivers' => 0,
            'totalLocations' => 0,
            'todayCollections' => 0,
            'recentActivities' => collect(),
            'performanceMetrics' => [
                'delivery_success_rate' => 0,
                'average_delivery_time' => 0,
                'collections_by_zone' => [],
                'driver_performance' => [],
                'weekly_trends' => [],
            ],
            'deliveryChart' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'completed' => array_fill(0, 7, 0),
                'total' => array_fill(0, 7, 0),
                'collections' => array_fill(0, 7, 0),
            ],
        ];
    }

    // Keep existing methods
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
            Log::error('Error calculating delivery success rate: ' . $e->getMessage());
            return 0;
        }
    }

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
            Log::error('Error getting collections by zone: ' . $e->getMessage());
            return [];
        }
    }

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
            Log::error('Error getting driver performance: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent activities for AJAX refresh
     */
    public function getActivities()
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

            return response()->json(['activities' => $activities]);
        } catch (\Exception $e) {
            Log::error('Error fetching activities: ' . $e->getMessage());
            return response()->json(['activities' => []], 500);
        }
    }

    public function driverDashboard()
    {
        // Driver dashboard implementation
        return view('driver.dashboard');
    }
}
