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
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view with analytics and metrics
     */
    public function index(Request $request)
    {
        try {
            // Cache key with hourly expiration for basic metrics
            $cacheKey = 'dashboard_metrics_' . date('Y-m-d_H');
            
            // Basic metrics with caching
            $basicMetrics = Cache::remember($cacheKey, 3600, function () {
                $totalRevenue = $this->getTotalRevenue();
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
                    'pendingDeliveries' => $this->getPendingDeliveries(),
                    'overdueDeliveries' => $this->getOverdueDeliveries(),
                    'totalRevenue' => $totalRevenue,
                    'averageRevenuePerDelivery' => $this->getAverageRevenuePerDelivery(),
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
                'on_time_delivery_rate' => $this->getOnTimeDeliveryRate(),
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
     * Get real-time metrics data for the dashboard
     */
    public function getMetricsData(): JsonResponse
    {
        try {
            // Basic metrics with shorter cache duration for real-time updates
            $basicMetrics = Cache::remember('dashboard_metrics_realtime', 30, function () {
                $totalRevenue = $this->getTotalRevenue();
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
                    'pendingDeliveries' => $this->getPendingDeliveries(),
                    'overdueDeliveries' => $this->getOverdueDeliveries(),
                    'totalRevenue' => $totalRevenue,
                    'averageRevenuePerDelivery' => $this->getAverageRevenuePerDelivery(),
                ];
            });

            // Get recent activities
            $recentActivities = ActivityLog::with('user')
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

            // Performance metrics
            $performanceMetrics = [
                'delivery_success_rate' => $this->getDeliverySuccessRate(),
                'average_delivery_time' => $this->getAverageDeliveryTime(),
                'collections_by_zone' => $this->getCollectionsByZone(),
                'driver_performance' => $this->getDriverPerformance(),
                'weekly_trends' => $this->getWeeklyTrends(),
                'on_time_delivery_rate' => $this->getOnTimeDeliveryRate(),
            ];

            // Chart data
            $deliveryChart = $this->getDeliveryChartData();

            return response()->json([
                'success' => true,
                'data' => [
                    'basicMetrics' => $basicMetrics,
                    'recentActivities' => $recentActivities,
                    'performanceMetrics' => $performanceMetrics,
                    'deliveryChart' => $deliveryChart,
                    'last_updated' => now()->toIso8601String()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching real-time metrics: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard metrics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    private function getFallbackData()
    {
        return [
            'totalZones' => 0,
            'activeDrivers' => 0,
            'totalLocations' => 0,
            'todayCollections' => 0,
            'pendingDeliveries' => 0,
            'overdueDeliveries' => 0,
            'totalRevenue' => 0,
            'averageRevenuePerDelivery' => 0,
            'recentActivities' => collect(),
            'performanceMetrics' => [
                'delivery_success_rate' => 0,
                'average_delivery_time' => 0,
                'collections_by_zone' => [],
                'driver_performance' => [],
                'weekly_trends' => [],
                'on_time_delivery_rate' => 0,
            ],
            'deliveryChart' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'completed' => array_fill(0, 7, 0),
                'total' => array_fill(0, 7, 0),
                'collections' => array_fill(0, 7, 0),
            ],
        ];
    }

    // Helper methods for metrics calculations
    private function getPendingDeliveries()
    {
        try {
            return Location::where('status', 'pending')->count();
        } catch (\Exception $e) {
            Log::error('Error calculating pending deliveries: ' . $e->getMessage());
            return 0;
        }
    }

    private function getOverdueDeliveries()
    {
        try {
            return Location::whereNotNull('started_at')
                ->whereNull('completed_at')
                ->where('status', '!=', 'completed')
                ->whereRaw('TIMESTAMPDIFF(MINUTE, started_at, NOW()) > 120')
                ->count();
        } catch (\Exception $e) {
            Log::error('Error calculating overdue deliveries: ' . $e->getMessage());
            return 0;
        }
    }

    private function getTotalRevenue()
    {
        try {
            return Location::where('status', 'completed')
                ->where('payment_received', true)
                ->sum('payment_amount_received') ?? 0;
        } catch (\Exception $e) {
            Log::error('Error calculating total revenue: ' . $e->getMessage());
            return 0;
        }
    }

    private function getAverageRevenuePerDelivery()
    {
        try {
            $completedCount = Location::where('status', 'completed')
                ->where('payment_received', true)
                ->count();

            if ($completedCount === 0) {
                return 0;
            }

            return Location::where('status', 'completed')
                ->where('payment_received', true)
                ->avg('payment_amount_received') ?? 0;
        } catch (\Exception $e) {
            Log::error('Error calculating average revenue per delivery: ' . $e->getMessage());
            return 0;
        }
    }

    private function getOnTimeDeliveryRate()
    {
        try {
            $completedDeliveries = Location::where('status', 'completed')
                ->whereNotNull('started_at')
                ->whereNotNull('completed_at')
                ->get();

            if ($completedDeliveries->isEmpty()) {
                return 0;
            }

            $onTimeCount = $completedDeliveries->filter(function ($location) {
                return $location->isOnTime();
            })->count();

            return round(($onTimeCount / $completedDeliveries->count()) * 100, 2);
        } catch (\Exception $e) {
            Log::error('Error calculating on-time delivery rate: ' . $e->getMessage());
            return 0;
        }
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

    private function getDeliveryChartData()
    {
        try {
            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $completed = array_fill(0, 7, 0);
            $total = array_fill(0, 7, 0);
            $collections = array_fill(0, 7, 0);
            $onTime = array_fill(0, 7, 0);

            $startOfWeek = now()->startOfWeek();
            for ($i = 0; $i < 7; $i++) {
                $date = $startOfWeek->copy()->addDays($i);
                
                // Get basic delivery stats
                $dayData = Location::whereDate('completed_at', $date)
                    ->selectRaw('COUNT(*) as total')
                    ->selectRaw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
                    ->selectRaw('SUM(CASE WHEN payment_received = true THEN payment_amount_received ELSE 0 END) as collections')
                    ->first();

                $completed[$i] = $dayData->completed ?? 0;
                $total[$i] = $dayData->total ?? 0;
                $collections[$i] = $dayData->collections ?? 0;

                // Calculate on-time deliveries for the day
                $dayDeliveries = Location::whereDate('completed_at', $date)
                    ->where('status', 'completed')
                    ->whereNotNull('started_at')
                    ->get();

                $onTime[$i] = $dayDeliveries->filter(function ($location) {
                    return $location->isOnTime();
                })->count();
            }

            // Calculate performance trends
            $prevWeekStart = $startOfWeek->copy()->subWeek();
            $prevWeekStats = Location::whereBetween('completed_at', [$prevWeekStart, $startOfWeek])
                ->selectRaw('COUNT(*) as total_deliveries')
                ->selectRaw('SUM(CASE WHEN payment_received = true THEN payment_amount_received ELSE 0 END) as total_revenue')
                ->first();

            $currentWeekStats = Location::whereBetween('completed_at', [$startOfWeek, now()])
                ->selectRaw('COUNT(*) as total_deliveries')
                ->selectRaw('SUM(CASE WHEN payment_received = true THEN payment_amount_received ELSE 0 END) as total_revenue')
                ->first();

            return [
                'labels' => $days,
                'completed' => $completed,
                'total' => $total,
                'collections' => $collections,
                'onTime' => $onTime,
                'trends' => [
                    'deliveries' => [
                        'current' => $currentWeekStats->total_deliveries ?? 0,
                        'previous' => $prevWeekStats->total_deliveries ?? 0,
                    ],
                    'revenue' => [
                        'current' => $currentWeekStats->total_revenue ?? 0,
                        'previous' => $prevWeekStats->total_revenue ?? 0,
                    ]
                ]
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

    /**
     * Get driver dashboard data
     */
    public function driverDashboard()
    {
        try {
            $driver = auth()->user();
            
            if (!$driver || $driver->role !== 'driver') {
                return redirect()->route('login');
            }

            $today = now()->startOfDay();
            
            $data = [
                'todayDeliveries' => Location::where('assigned_to', $driver->id)
                    ->whereDate('created_at', $today)
                    ->count(),
                    
                'completedDeliveries' => Location::where('assigned_to', $driver->id)
                    ->where('status', 'completed')
                    ->whereDate('completed_at', $today)
                    ->count(),
                    
                'totalCollections' => Location::where('assigned_to', $driver->id)
                    ->where('status', 'completed')
                    ->where('payment_received', true)
                    ->whereDate('completed_at', $today)
                    ->sum('payment_amount_received') ?? 0,
                    
                'pendingDeliveries' => Location::where('assigned_to', $driver->id)
                    ->where('status', 'pending')
                    ->count(),
                    
                'performance' => [
                    'success_rate' => $this->getDriverSuccessRate($driver->id),
                    'average_time' => $this->getDriverAverageTime($driver->id),
                    'on_time_rate' => $this->getDriverOnTimeRate($driver->id)
                ],
                
                'recentDeliveries' => Location::where('assigned_to', $driver->id)
                    ->with(['zone'])
                    ->latest()
                    ->take(5)
                    ->get()
            ];

            return view('driver.dashboard', $data);
        } catch (\Exception $e) {
            Log::error('Error loading driver dashboard: ' . $e->getMessage());
            return view('driver.dashboard')->with('error', 'Unable to load dashboard data');
        }
    }

    /**
     * Get driver's delivery success rate
     */
    private function getDriverSuccessRate($driverId)
    {
        try {
            $totalDeliveries = Location::where('assigned_to', $driverId)
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->count();

            if ($totalDeliveries === 0) {
                return 0;
            }

            $successfulDeliveries = Location::where('assigned_to', $driverId)
                ->where('status', 'completed')
                ->whereDate('completed_at', '>=', now()->subDays(30))
                ->count();

            return round(($successfulDeliveries / $totalDeliveries) * 100, 2);
        } catch (\Exception $e) {
            Log::error('Error calculating driver success rate: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get driver's average delivery time
     */
    private function getDriverAverageTime($driverId)
    {
        try {
            return Location::where('assigned_to', $driverId)
                ->where('status', 'completed')
                ->whereNotNull('started_at')
                ->whereNotNull('completed_at')
                ->whereDate('completed_at', '>=', now()->subDays(30))
                ->get()
                ->avg(function ($location) {
                    return $location->started_at->diffInMinutes($location->completed_at);
                }) ?? 0;
        } catch (\Exception $e) {
            Log::error('Error calculating driver average time: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get driver's on-time delivery rate
     */
    private function getDriverOnTimeRate($driverId)
    {
        try {
            $completedDeliveries = Location::where('assigned_to', $driverId)
                ->where('status', 'completed')
                ->whereNotNull('started_at')
                ->whereNotNull('completed_at')
                ->whereDate('completed_at', '>=', now()->subDays(30))
                ->get();

            if ($completedDeliveries->isEmpty()) {
                return 0;
            }

            $onTimeCount = $completedDeliveries->filter(function ($location) {
                return $location->isOnTime();
            })->count();

            return round(($onTimeCount / $completedDeliveries->count()) * 100, 2);
        } catch (\Exception $e) {
            Log::error('Error calculating driver on-time rate: ' . $e->getMessage());
            return 0;
        }
    }
}
