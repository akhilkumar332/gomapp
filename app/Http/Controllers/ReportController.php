<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\User;
use App\Models\Zone;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $totalDeliveries = Location::count();
        $totalCollections = Location::where('payment_received', true)->sum('payment_amount_received');
        $activeDrivers = User::where('role', 'driver')
            ->whereNotNull('last_location_update')
            ->where('last_location_update', '>=', now()->subHour())
            ->count();
        $totalLocations = Location::count();

        return view('admin.reports.index', compact(
            'totalDeliveries',
            'totalCollections',
            'activeDrivers',
            'totalLocations'
        ));
    }

    public function activity()
    {
        $activities = ActivityLog::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.reports.activity', compact('activities'));
    }

    public function performance()
    {
        $drivers = User::where('role', 'driver')
            ->select('users.*')
            ->withCount(['completedLocations' => function ($query) {
                $query->whereDate('completed_at', '>=', now()->subDays(30));
            }])
            ->withSum(['completedLocations' => function ($query) {
                $query->whereDate('completed_at', '>=', now()->subDays(30))
                    ->where('payment_received', true);
            }], 'payment_amount_received')
            ->groupBy('users.id')
            ->having('completed_locations_count', '>', 0)
            ->get();

        $zones = Zone::with(['locations' => function ($query) {
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

        return view('admin.reports.performance', compact('drivers', 'zones'));
    }

    public function export()
    {
        $now = Carbon::now();
        
        // Get monthly statistics
        $monthlyStats = [
            'total_deliveries' => Location::whereMonth('created_at', $now->month)->count(),
            'completed_deliveries' => Location::where('status', 'completed')
                ->whereMonth('completed_at', $now->month)
                ->count(),
            'total_collections' => Location::where('payment_received', true)
                ->whereMonth('completed_at', $now->month)
                ->sum('payment_amount_received'),
            'active_drivers' => User::where('role', 'driver')
                ->whereNotNull('last_location_update')
                ->where('last_location_update', '>=', $now->subHour())
                ->count(),
        ];

        // Get driver performance
        $driverPerformance = User::where('role', 'driver')
            ->select('users.*')
            ->withCount(['completedLocations' => function ($query) use ($now) {
                $query->whereMonth('completed_at', $now->month);
            }])
            ->withSum(['completedLocations' => function ($query) use ($now) {
                $query->whereMonth('completed_at', $now->month)
                    ->where('payment_received', true);
            }], 'payment_amount_received')
            ->groupBy('users.id')
            ->having('completed_locations_count', '>', 0)
            ->get();

        // Get zone statistics
        $zoneStats = Zone::withCount(['locations' => function ($query) use ($now) {
            $query->whereMonth('created_at', $now->month);
        }])
        ->withSum(['locations' => function ($query) use ($now) {
            $query->whereMonth('completed_at', $now->month)
                ->where('payment_received', true);
        }], 'payment_amount_received')
        ->get();

        return view('admin.reports.export', compact('monthlyStats', 'driverPerformance', 'zoneStats'));
    }
}
