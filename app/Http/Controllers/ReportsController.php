<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\LoginLog;
use App\Models\Payment;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Display the reports dashboard.
     */
    public function index()
    {
        $driverActivityCount = ActivityLog::whereHas('user', function($query) {
            $query->where('role', 'driver');
        })->count();

        $zoneStatisticsCount = Zone::count();

        $systemUsageCount = LoginLog::count();

        return view('admin.reports.index', compact('driverActivityCount', 'zoneStatisticsCount', 'systemUsageCount'));
    }

    /**
     * Display the driver activity report.
     */
    public function driverActivity(Request $request)
    {
        $query = ActivityLog::with('user')
            ->whereHas('user', function($query) {
                $query->where('role', 'driver');
            });

        if ($request->driver_id) {
            $query->where('user_id', $request->driver_id);
        }

        if ($request->date_range) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            }
        }

        $activityLogs = $query->orderBy('created_at', 'desc')->paginate(20);
        $drivers = User::where('role', 'driver')->get();

        return view('admin.reports.driver-activity', compact('activityLogs', 'drivers'));
    }

    /**
     * Display the zone statistics report.
     */
    public function zoneStatistics(Request $request)
    {
        $query = Zone::with(['locations', 'drivers']);

        if ($request->zone_id) {
            $query->where('id', $request->zone_id);
        }

        $zones = $query->get();
        $statistics = $this->calculateZoneStatistics($zones);

        return view('admin.reports.zone-statistics', compact('zones', 'statistics'));
    }

    /**
     * Display the system usage report.
     */
    public function systemUsage(Request $request)
    {
        $dateRange = $this->parseDateRange($request->date_range);
        $userType = $request->user_type;

        $metrics = $this->calculateSystemMetrics($dateRange, $userType);
        $activityData = $this->getActivityData($dateRange, $userType);
        $peakHours = $this->getPeakHours($dateRange, $userType);
        $usageLogs = $this->getUsageLogs($dateRange, $userType);

        return view('admin.reports.system-usage', compact('metrics', 'activityData', 'peakHours', 'usageLogs'));
    }

    /**
     * Export driver activity report.
     */
    public function exportDriverActivity(Request $request)
    {
        $query = ActivityLog::with('user')
            ->whereHas('user', function($query) {
                $query->where('role', 'driver');
            });

        if ($request->driver_id) {
            $query->where('user_id', $request->driver_id);
        }

        if ($request->date_range) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            }
        }

        $logs = $query->get();

        return response()->json($logs);
    }

    /**
     * Export zone statistics report.
     */
    public function exportZoneStatistics(Request $request)
    {
        $query = Zone::with(['locations', 'drivers']);

        if ($request->zone_id) {
            $query->where('id', $request->zone_id);
        }

        $zones = $query->get();
        $statistics = $this->calculateZoneStatistics($zones);

        return response()->json($statistics);
    }

    /**
     * Export system usage report.
     */
    public function exportSystemUsage(Request $request)
    {
        $dateRange = $this->parseDateRange($request->date_range);
        $userType = $request->user_type;

        $logs = $this->getUsageLogs($dateRange, $userType);

        return response()->json($logs);
    }

    /**
     * Get driver's personal activity report.
     */
    public function driverPersonalActivity(Request $request)
    {
        $logs = ActivityLog::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($logs);
    }

    /**
     * Get driver's personal statistics.
     */
    public function driverPersonalStatistics(Request $request)
    {
        $statistics = [
            'total_deliveries' => 0, // Implement actual delivery count
            'success_rate' => 0, // Implement actual success rate calculation
            'average_time' => 0, // Implement actual average time calculation
            'total_zones' => Auth::user()->zones()->count()
        ];

        return response()->json($statistics);
    }

    /**
     * Calculate zone statistics.
     */
    private function calculateZoneStatistics($zones)
    {
        return $zones->map(function ($zone) {
            return [
                'zone_name' => $zone->name,
                'total_locations' => $zone->locations->count(),
                'active_drivers' => $zone->drivers->where('status', 'active')->count(),
                'total_deliveries' => $zone->locations->sum('deliveries_count'),
                'success_rate' => $this->calculateSuccessRate($zone)
            ];
        });
    }

    /**
     * Calculate success rate for a zone.
     */
    private function calculateSuccessRate($zone)
    {
        $totalDeliveries = $zone->locations->sum('deliveries_count');
        $successfulDeliveries = $zone->locations->sum('successful_deliveries');

        return $totalDeliveries > 0 ? ($successfulDeliveries / $totalDeliveries) * 100 : 0;
    }

    /**
     * Parse date range from request.
     */
    private function parseDateRange($dateRange)
    {
        if ($dateRange) {
            $dates = explode(' - ', $dateRange);
            if (count($dates) == 2) {
                return [
                    'start' => Carbon::parse($dates[0])->startOfDay(),
                    'end' => Carbon::parse($dates[1])->endOfDay()
                ];
            }
        }

        return [
            'start' => Carbon::now()->subDays(30)->startOfDay(),
            'end' => Carbon::now()->endOfDay()
        ];
    }

    /**
     * Calculate system metrics.
     */
    private function calculateSystemMetrics($dateRange, $userType)
    {
        $query = LoginLog::whereBetween('login_at', [$dateRange['start'], $dateRange['end']]);

        if ($userType) {
            $query->whereHas('user', function($q) use ($userType) {
                $q->where('role', $userType);
            });
        }

        $totalSessions = $query->count();
        $activeUsers = $query->distinct('user_id')->count();

        return [
            'active_users' => $activeUsers,
            'total_sessions' => $totalSessions,
            'avg_session_duration' => '30m', // Implement actual calculation
            'error_rate' => '0.5%' // Implement actual calculation
        ];
    }

    /**
     * Get activity data for charts.
     */
    private function getActivityData($dateRange, $userType)
    {
        $query = ActivityLog::whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);

        if ($userType) {
            $query->whereHas('user', function($q) use ($userType) {
                $q->where('role', $userType);
            });
        }

        $activities = $query->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'dates' => $activities->pluck('date'),
            'counts' => $activities->pluck('count')
        ];
    }

    /**
     * Get peak usage hours.
     */
    private function getPeakHours($dateRange, $userType)
    {
        $query = LoginLog::whereBetween('login_at', [$dateRange['start'], $dateRange['end']]);

        if ($userType) {
            $query->whereHas('user', function($q) use ($userType) {
                $q->where('role', $userType);
            });
        }

        $peakHours = $query->select(DB::raw('HOUR(login_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return [
            'hours' => $peakHours->pluck('hour'),
            'counts' => $peakHours->pluck('count')
        ];
    }

    /**
     * Get usage logs.
     */
    private function getUsageLogs($dateRange, $userType)
    {
        $query = ActivityLog::with('user')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);

        if ($userType) {
            $query->whereHas('user', function($q) use ($userType) {
                $q->where('role', $userType);
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($log) {
                return [
                    'timestamp' => $log->created_at,
                    'user' => $log->user->name,
                    'action' => $log->action,
                    'module' => $log->module ?? 'N/A',
                    'duration' => '5m', // Implement actual duration calculation
                    'status' => 'success' // Implement actual status determination
                ];
            });
    }
}
