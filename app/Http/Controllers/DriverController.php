<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Zone;
use App\Models\Payment;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    /**
     * Display the driver's dashboard.
     */
    public function dashboard()
    {
        $driver = Auth::user();
        
        // Get today's statistics
        $todayDeliveries = ActivityLog::where('user_id', $driver->id)
            ->whereDate('created_at', Carbon::today())
            ->where('action', 'delivery')
            ->count();

        $todayCollections = Payment::where('collected_by', $driver->id)
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        // Calculate success rate
        $totalDeliveries = ActivityLog::where('user_id', $driver->id)
            ->where('action', 'delivery')
            ->count();
            
        $successfulDeliveries = ActivityLog::where('user_id', $driver->id)
            ->where('action', 'delivery')
            ->where('status', 'success')
            ->count();
            
        $successRate = $totalDeliveries > 0 ? 
            round(($successfulDeliveries / $totalDeliveries) * 100) : 0;

        // Get assigned zones
        $assignedZones = $driver->zones()
            ->withCount('locations')
            ->get();
        $assignedZonesCount = $assignedZones->count();

        // Get recent activities
        $recentActivities = ActivityLog::where('user_id', $driver->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get performance data for chart (last 7 days)
        $performanceData = $this->getPerformanceData($driver->id);

        return view('driver.dashboard', compact(
            'todayDeliveries',
            'todayCollections',
            'successRate',
            'assignedZones',
            'assignedZonesCount',
            'recentActivities',
            'performanceData'
        ));
    }

    /**
     * Update driver's online status.
     */
    public function updateStatus(Request $request)
    {
        $driver = Auth::user();
        $driver->is_online = $request->is_online;
        $driver->save();

        // Log the status change
        ActivityLog::create([
            'user_id' => $driver->id,
            'action' => 'status_change',
            'description' => 'Driver went ' . ($request->is_online ? 'online' : 'offline'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Ping to keep driver's online status active.
     */
    public function ping()
    {
        $driver = Auth::user();
        $driver->last_ping = now();
        $driver->save();

        return response()->json(['status' => 'success']);
    }

    /**
     * Get driver's performance data for the last 7 days.
     */
    private function getPerformanceData($driverId)
    {
        $data = [];
        $startDate = Carbon::now()->subDays(6);

        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $data['labels'][] = $date->format('M d');

            // Get successful deliveries
            $data['success'][] = ActivityLog::where('user_id', $driverId)
                ->whereDate('created_at', $date)
                ->where('action', 'delivery')
                ->where('status', 'success')
                ->count();

            // Get failed deliveries
            $data['failed'][] = ActivityLog::where('user_id', $driverId)
                ->whereDate('created_at', $date)
                ->where('action', 'delivery')
                ->where('status', 'failed')
                ->count();
        }

        return $data;
    }

    /**
     * Display the driver's assigned zones.
     */
    public function zones()
    {
        $zones = Auth::user()->zones()
            ->withCount(['locations', 'drivers'])
            ->get();

        return view('driver.zones.index', compact('zones'));
    }

    /**
     * Display the driver's activity log.
     */
    public function activity(Request $request)
    {
        $query = ActivityLog::where('user_id', Auth::id());

        if ($request->action) {
            $query->where('action', $request->action);
        }

        if ($request->date_range) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            }
        }

        $activities = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('driver.activity', compact('activities'));
    }

    /**
     * Display the driver's payments.
     */
    public function payments(Request $request)
    {
        $query = Payment::where('collected_by', Auth::id());

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_range) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            }
        }

        $payments = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('driver.payments', compact('payments'));
    }
}
