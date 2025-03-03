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
                try {
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
                        'averageRevenuePerDelivery' => $this->getAverageRevenuePerDelivery()
                    ];
                } catch (\Exception $e) {
                    Log::error('Error calculating basic metrics: ' . $e->getMessage());
                    return [
                        'totalZones' => 0,
                        'activeDrivers' => 0,
                        'totalLocations' => 0,
                        'todayCollections' => 0,
                        'pendingDeliveries' => 0,
                        'overdueDeliveries' => 0,
                        'totalRevenue' => 0,
                        'averageRevenuePerDelivery' => 0
                    ];
                }
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
                'driver_utilization_rate' => $this->getDriverUtilizationRate(),
                'delivery_delay_rate' => $this->getDeliveryDelayRate(),
                'revenue_growth_rate' => $this->getRevenueGrowthRate(),
                'efficiency_index' => $this->getEfficiencyIndex(),
                'delivery_volume_trend' => $this->getDeliveryVolumeTrend(),
            ];

            // Enhanced delivery chart data with weekly comparison
            $deliveryChart = $this->getDeliveryChartData();

            // Get business insights
            $businessInsights = [
                'topZones' => $this->getTopPerformingZones(),
                'customerSatisfaction' => $this->getCustomerSatisfactionMetrics(),
                'businessHealth' => $this->getBusinessHealthMetrics()
            ];

            // Combine all data
            $data = array_merge($basicMetrics, [
                'recentActivities' => $recentActivities,
                'performanceMetrics' => $performanceMetrics,
                'deliveryChart' => $deliveryChart,
                'topZones' => $businessInsights['topZones'],
                'customerSatisfaction' => $businessInsights['customerSatisfaction'],
                'businessHealth' => $businessInsights['businessHealth']
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
                try {
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
                        'averageRevenuePerDelivery' => $this->getAverageRevenuePerDelivery()
                    ];
                } catch (\Exception $e) {
                    Log::error('Error calculating basic metrics: ' . $e->getMessage());
                    return [
                        'totalZones' => 0,
                        'activeDrivers' => 0,
                        'totalLocations' => 0,
                        'todayCollections' => 0,
                        'pendingDeliveries' => 0,
                        'overdueDeliveries' => 0,
                        'totalRevenue' => 0,
                        'averageRevenuePerDelivery' => 0
                    ];
                }
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
                'driver_utilization_rate' => $this->getDriverUtilizationRate(),
                'delivery_delay_rate' => $this->getDeliveryDelayRate(),
                'revenue_growth_rate' => $this->getRevenueGrowthRate(),
                'efficiency_index' => $this->getEfficiencyIndex(),
                'delivery_volume_trend' => $this->getDeliveryVolumeTrend(),
            ];

            // Business Insights
            $businessInsights = [
                'topZones' => $this->getTopPerformingZones(),
                'customerSatisfaction' => $this->getCustomerSatisfactionMetrics(),
                'businessHealth' => $this->getBusinessHealthMetrics()
            ];

            // Chart data
            $deliveryChart = $this->getDeliveryChartData();

            return response()->json([
                'success' => true,
                'data' => [
                    'basicMetrics' => $basicMetrics,
                    'recentActivities' => $recentActivities,
                    'performanceMetrics' => $performanceMetrics,
                    'businessInsights' => $businessInsights,
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
                'driver_utilization_rate' => 0,
                'delivery_delay_rate' => 0,
                'revenue_growth_rate' => 0,
                'efficiency_index' => 0,
                'delivery_volume_trend' => [
                    'current' => 0,
                    'previous' => 0,
                    'growth' => 0
                ],
            ],
            'deliveryChart' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'completed' => array_fill(0, 7, 0),
                'total' => array_fill(0, 7, 0),
                'collections' => array_fill(0, 7, 0),
            ],
            // Business Insights fallback data
            'topZones' => collect(),
            'customerSatisfaction' => [
                'rating' => 0,
                'positive' => 0,
                'neutral' => 0,
                'negative' => 0
            ],
            'businessHealth' => [
                'retention_rate' => 0,
                'driver_availability' => 0,
                'system_uptime' => 0
            ]
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

    private function getDriverUtilizationRate()
    {
        try {
            $totalDrivers = User::where('role', 'driver')->count();
            
            if ($totalDrivers === 0) {
                return 0;
            }

            $activeDrivers = User::where('role', 'driver')
                ->whereNotNull('last_location_update')
                ->where('last_location_update', '>=', now()->subHour())
                ->count();

            return round(($activeDrivers / $totalDrivers) * 100, 2);
        } catch (\Exception $e) {
            Log::error('Error calculating driver utilization rate: ' . $e->getMessage());
            return 0;
        }
    }

    private function getDeliveryDelayRate()
    {
        try {
            $totalDeliveries = Location::whereDate('created_at', '>=', now()->subDays(30))->count();
            
            if ($totalDeliveries === 0) {
                return 0;
            }

            $overdueDeliveries = Location::whereNotNull('started_at')
                ->whereNull('completed_at')
                ->where('status', '!=', 'completed')
                ->whereRaw('TIMESTAMPDIFF(MINUTE, started_at, NOW()) > 120')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->count();

            return round(($overdueDeliveries / $totalDeliveries) * 100, 2);
        } catch (\Exception $e) {
            Log::error('Error calculating delivery delay rate: ' . $e->getMessage());
            return 0;
        }
    }

    private function getRevenueGrowthRate()
    {
        try {
            $currentWeekRevenue = Location::where('status', 'completed')
                ->where('payment_received', true)
                ->whereBetween('completed_at', [now()->startOfWeek(), now()])
                ->sum('payment_amount_received') ?? 0;

            $previousWeekRevenue = Location::where('status', 'completed')
                ->where('payment_received', true)
                ->whereBetween('completed_at', [
                    now()->subWeek()->startOfWeek(),
                    now()->subWeek()->endOfWeek()
                ])
                ->sum('payment_amount_received') ?? 0;

            if ($previousWeekRevenue === 0) {
                return $currentWeekRevenue > 0 ? 100 : 0;
            }

            return round((($currentWeekRevenue - $previousWeekRevenue) / $previousWeekRevenue) * 100, 2);
        } catch (\Exception $e) {
            Log::error('Error calculating revenue growth rate: ' . $e->getMessage());
            return 0;
        }
    }

    private function getEfficiencyIndex()
    {
        try {
            $completedDeliveries = Location::where('status', 'completed')
                ->where('payment_received', true)
                ->whereNotNull('started_at')
                ->whereNotNull('completed_at')
                ->whereDate('completed_at', '>=', now()->subDays(30))
                ->get();

            if ($completedDeliveries->isEmpty()) {
                return 0;
            }

            $totalRevenue = $completedDeliveries->sum('payment_amount_received');
            $totalMinutes = $completedDeliveries->sum(function ($delivery) {
                return $delivery->started_at->diffInMinutes($delivery->completed_at);
            });

            return $totalMinutes > 0 ? round($totalRevenue / $totalMinutes, 2) : 0;
        } catch (\Exception $e) {
            Log::error('Error calculating efficiency index: ' . $e->getMessage());
            return 0;
        }
    }

    private function getDeliveryVolumeTrend()
    {
        try {
            $currentWeekVolume = Location::where('status', 'completed')
                ->whereBetween('completed_at', [now()->startOfWeek(), now()])
                ->count();

            $previousWeekVolume = Location::where('status', 'completed')
                ->whereBetween('completed_at', [
                    now()->subWeek()->startOfWeek(),
                    now()->subWeek()->endOfWeek()
                ])
                ->count();

            return [
                'current' => $currentWeekVolume,
                'previous' => $previousWeekVolume,
                'growth' => $previousWeekVolume > 0 
                    ? round((($currentWeekVolume - $previousWeekVolume) / $previousWeekVolume) * 100, 2)
                    : ($currentWeekVolume > 0 ? 100 : 0)
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating delivery volume trend: ' . $e->getMessage());
            return ['current' => 0, 'previous' => 0, 'growth' => 0];
        }
    }

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

            $startOfWeek = now()->startOfWeek();
            for ($i = 0; $i < 7; $i++) {
                $date = $startOfWeek->copy()->addDays($i);
                
                // Get total deliveries for the day
                $totalDeliveries = Location::whereDate('created_at', $date)->count();
                
                // Get completed deliveries
                $completedDeliveries = Location::whereDate('completed_at', $date)
                    ->where('status', 'completed')
                    ->count();
                
                // Get collections
                $dayCollections = Location::whereDate('completed_at', $date)
                    ->where('status', 'completed')
                    ->where('payment_received', true)
                    ->sum('payment_amount_received') ?? 0;

                $completed[$i] = $completedDeliveries;
                $total[$i] = $totalDeliveries;
                $collections[$i] = $dayCollections;
            }

            // Add some sample data for testing (remove in production)
            if (array_sum($total) === 0) {
                $completed = [5, 8, 12, 7, 9, 6, 4];
                $total = [8, 12, 15, 10, 14, 9, 7];
                $collections = [500, 800, 1200, 700, 900, 600, 400];
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

    /**
     * Get top performing zones based on delivery volume and revenue
     */
    private function getTopPerformingZones()
    {
        try {
            return Zone::with(['locations' => function ($query) {
                $query->where('status', 'completed')
                    ->where('payment_received', true)
                    ->whereDate('completed_at', '>=', now()->subDays(30));
            }])
            ->get()
            ->map(function ($zone) {
                $currentRevenue = $zone->locations->sum('payment_amount_received');
                
                // Calculate previous period revenue
                $previousRevenue = Location::where('zone_id', $zone->id)
                    ->where('status', 'completed')
                    ->where('payment_received', true)
                    ->whereBetween('completed_at', [
                        now()->subDays(60),
                        now()->subDays(30)
                    ])
                    ->sum('payment_amount_received');

                // Calculate growth
                $growth = $previousRevenue > 0 
                    ? round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 2)
                    : ($currentRevenue > 0 ? 100 : 0);

                return [
                    'name' => $zone->name,
                    'deliveries' => $zone->locations->count(),
                    'revenue' => $currentRevenue,
                    'growth' => $growth
                ];
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values();
        } catch (\Exception $e) {
            Log::error('Error calculating top performing zones: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get customer satisfaction metrics
     */
    private function getCustomerSatisfactionMetrics()
    {
        try {
            $deliveries = Location::where('status', 'completed')
                ->whereNotNull('customer_rating')
                ->whereDate('completed_at', '>=', now()->subDays(30))
                ->get();

            if ($deliveries->isEmpty()) {
                return [
                    'rating' => 0,
                    'positive' => 0,
                    'neutral' => 0,
                    'negative' => 0
                ];
            }

            $totalRatings = $deliveries->count();
            $averageRating = $deliveries->avg('customer_rating');

            $positive = $deliveries->filter(fn($d) => $d->customer_rating >= 4)->count();
            $neutral = $deliveries->filter(fn($d) => $d->customer_rating == 3)->count();
            $negative = $deliveries->filter(fn($d) => $d->customer_rating <= 2)->count();

            return [
                'rating' => round($averageRating, 1),
                'positive' => round(($positive / $totalRatings) * 100),
                'neutral' => round(($neutral / $totalRatings) * 100),
                'negative' => round(($negative / $totalRatings) * 100)
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating customer satisfaction metrics: ' . $e->getMessage());
            return [
                'rating' => 0,
                'positive' => 0,
                'neutral' => 0,
                'negative' => 0
            ];
        }
    }

    /**
     * Get business health metrics
     */
    private function getBusinessHealthMetrics()
    {
        try {
            // Calculate customer retention rate
            $previousCustomers = Location::select('customer_id')
                ->whereBetween('completed_at', [
                    now()->subDays(60),
                    now()->subDays(30)
                ])
                ->distinct()
                ->get();

            $retainedCustomers = Location::select('customer_id')
                ->whereIn('customer_id', $previousCustomers->pluck('customer_id'))
                ->whereDate('completed_at', '>=', now()->subDays(30))
                ->distinct()
                ->count();

            $retentionRate = $previousCustomers->count() > 0
                ? round(($retainedCustomers / $previousCustomers->count()) * 100, 2)
                : 0;

            // Calculate driver availability
            $totalDrivers = User::where('role', 'driver')->count();
            $availableDrivers = User::where('role', 'driver')
                ->whereNotNull('last_location_update')
                ->where('last_location_update', '>=', now()->subMinutes(30))
                ->count();

            $driverAvailability = $totalDrivers > 0
                ? round(($availableDrivers / $totalDrivers) * 100, 2)
                : 0;

            // Calculate system uptime (mock data - replace with actual monitoring)
            $systemUptime = 99.9;

            return [
                'retention_rate' => $retentionRate,
                'driver_availability' => $driverAvailability,
                'system_uptime' => $systemUptime
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating business health metrics: ' . $e->getMessage());
            return [
                'retention_rate' => 0,
                'driver_availability' => 0,
                'system_uptime' => 0
            ];
        }
    }
}
