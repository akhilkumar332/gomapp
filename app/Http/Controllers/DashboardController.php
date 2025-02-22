<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\User;
use App\Models\Location;
use App\Models\Payment;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Get quick stats
        $totalZones = Zone::count();
        $activeDrivers = User::where('role', 'driver')
            ->where('status', 'active')
            ->count();
        $totalLocations = Location::count();
        $todayPayments = Payment::whereDate('created_at', Carbon::today())
            ->sum('amount');

        // Get recent activities
        $recentActivities = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get activity data for chart (last 7 days)
        $activityData = $this->getActivityChartData();

        // Get report counts
        $driverActivityCount = ActivityLog::whereHas('user', function($query) {
            $query->where('role', 'driver');
        })->count();

        $zoneStatisticsCount = Zone::count();

        $systemUsageCount = DB::table('login_logs')->count();

        // Get server health metrics (cached for 5 minutes)
        $serverHealth = Cache::remember('server_health', 300, function () {
            return $this->getServerHealth();
        });

        return view('admin.dashboard', compact(
            'totalZones',
            'activeDrivers',
            'totalLocations',
            'todayPayments',
            'recentActivities',
            'activityData',
            'driverActivityCount',
            'zoneStatisticsCount',
            'systemUsageCount',
            'serverHealth'
        ));
    }

    /**
     * Get activity data for the chart.
     */
    private function getActivityChartData()
    {
        $activities = ActivityLog::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->whereDate('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $activities->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('M d');
            }),
            'values' => $activities->pluck('count')
        ];
    }

    /**
     * Get server health metrics.
     */
    private function getServerHealth()
    {
        // CPU Usage
        $cpuUsage = sys_getloadavg()[0] * 100 / cpu_count();

        // Memory Usage
        $memoryTotal = memory_get_total();
        $memoryFree = memory_get_free();
        $memoryUsage = ($memoryTotal - $memoryFree) / $memoryTotal * 100;

        // Disk Usage
        $diskTotal = disk_total_space('/');
        $diskFree = disk_free_space('/');
        $diskUsage = ($diskTotal - $diskFree) / $diskTotal * 100;

        return [
            'cpu_usage' => round($cpuUsage, 2),
            'memory_usage' => round($memoryUsage, 2),
            'disk_usage' => round($diskUsage, 2),
            'last_checked' => Carbon::now()
        ];
    }

    /**
     * Get total memory in bytes.
     */
    private function memory_get_total()
    {
        $meminfo = file_get_contents('/proc/meminfo');
        preg_match('/MemTotal:\s+(\d+)\skB/', $meminfo, $matches);
        return $matches[1] * 1024;
    }

    /**
     * Get free memory in bytes.
     */
    private function memory_get_free()
    {
        $meminfo = file_get_contents('/proc/meminfo');
        preg_match('/MemFree:\s+(\d+)\skB/', $meminfo, $matches);
        return $matches[1] * 1024;
    }

    /**
     * Get number of CPU cores.
     */
    private function cpu_count()
    {
        return (int) shell_exec('nproc');
    }
}
