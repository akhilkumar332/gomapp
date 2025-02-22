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
     * Display a listing of the drivers.
     */
    public function index()
    {
        $drivers = User::where('role', 'driver')
            ->withCount(['zones', 'activityLogs'])
            ->paginate(10);
        return view('admin.drivers.index', compact('drivers'));
    }

    /**
     * Show the form for creating a new driver.
     */
    public function create()
    {
        $zones = Zone::all();
        return view('admin.drivers.create', compact('zones'));
    }

    /**
     * Store a newly created driver in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|string|unique:users,phone_number',
            'password' => 'required|string|min:8',
            'zones' => 'array'
        ]);

        $driver = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'password' => bcrypt($validated['password']),
            'role' => 'driver'
        ]);

        if (!empty($validated['zones'])) {
            $driver->zones()->attach($validated['zones']);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_driver',
            'description' => "Created new driver: {$driver->name}"
        ]);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver created successfully');
    }

    /**
     * Show the form for editing the specified driver.
     */
    public function edit(User $driver)
    {
        if ($driver->role !== 'driver') {
            abort(404);
        }
        
        $zones = Zone::all();
        $assignedZones = $driver->zones->pluck('id')->toArray();
        
        return view('admin.drivers.edit', compact('driver', 'zones', 'assignedZones'));
    }

    /**
     * Update the specified driver in storage.
     */
    public function update(Request $request, User $driver)
    {
        if ($driver->role !== 'driver') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $driver->id,
            'phone_number' => 'required|string|unique:users,phone_number,' . $driver->id,
            'password' => 'nullable|string|min:8',
            'zones' => 'array'
        ]);

        $driver->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number']
        ]);

        if (!empty($validated['password'])) {
            $driver->update(['password' => bcrypt($validated['password'])]);
        }

        $driver->zones()->sync($validated['zones'] ?? []);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_driver',
            'description' => "Updated driver: {$driver->name}"
        ]);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver updated successfully');
    }

    /**
     * Remove the specified driver from storage.
     */
    public function destroy(User $driver)
    {
        if ($driver->role !== 'driver') {
            abort(404);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_driver',
            'description' => "Deleted driver: {$driver->name}"
        ]);

        $driver->delete();

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver deleted successfully');
    }

    /**
     * Bulk delete drivers
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        $drivers = User::whereIn('id', $validated['ids'])
            ->where('role', 'driver')
            ->get();

        foreach ($drivers as $driver) {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete_driver',
                'description' => "Deleted driver: {$driver->name}"
            ]);
        }

        User::whereIn('id', $validated['ids'])
            ->where('role', 'driver')
            ->delete();

        return response()->json(['message' => 'Drivers deleted successfully']);
    }

    /**
     * Import drivers from CSV/Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx'
        ]);

        // Implementation for importing drivers
        // This would typically use Laravel Excel or similar package

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Drivers imported successfully');
    }

    /**
     * Export drivers to CSV/Excel
     */
    public function export()
    {
        $drivers = User::where('role', 'driver')
            ->with('zones')
            ->get();

        // Implementation for exporting drivers
        // This would typically use Laravel Excel or similar package

        return response()->download('drivers.csv');
    }

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
