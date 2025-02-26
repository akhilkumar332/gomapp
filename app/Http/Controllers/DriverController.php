<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as RequestFacade;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'driver')->with(['zones']);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('phone')) {
            $query->where('phone_number', 'like', '%' . $request->phone . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $drivers = $query->latest()->paginate(10)->withQueryString();

        if ($request->ajax()) {
            $html = view('admin.drivers.partials.driver-list', compact('drivers'))->render();
            return response()->json(['html' => $html]);
        }

        return view('admin.drivers.index', compact('drivers'));
    }

    public function create()
    {
        $zones = Zone::all();
        return view('admin.drivers.create', compact('zones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
            'password' => 'required|string|min:8',
            'status' => 'required|in:active,inactive',
            'zones' => 'nullable|array',
            'zones.*' => 'exists:zones,id',
        ]);

        $phone = $validated['phone_number'];
        if (!str_starts_with($phone, '+233')) {
            $phone = '+233' . ltrim($phone, '0');
        }

        $driver = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $phone,
            'password' => bcrypt($validated['password']),
            'status' => $validated['status'],
            'role' => 'driver',
        ]);

        if (!empty($validated['zones'])) {
            $driver->zones()->attach($validated['zones']);
        }

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver created successfully.');
    }

    public function show(User $driver)
    {
        $driver->load('zones');

        // Calculate basic statistics
        $stats = [
            'total_deliveries' => $driver->completedLocations()->count(),
            'total_collections' => $driver->completedLocations()->sum('payment_amount_received'),
            'avg_deliveries_per_day' => round($driver->completedLocations()
                ->where('completed_at', '>=', now()->subDays(30))
                ->count() / 30, 1),
            'active_hours' => $this->calculateActiveHours($driver, 'month'),
        ];

        // Calculate period-specific stats
        $stats = array_merge($stats, [
            'deliveries_7d' => $driver->completedLocations()
                ->where('completed_at', '>=', now()->subDays(7))
                ->count(),
            'deliveries_30d' => $driver->completedLocations()
                ->where('completed_at', '>=', now()->subDays(30))
                ->count(),
            'collections_7d' => $driver->completedLocations()
                ->where('completed_at', '>=', now()->subDays(7))
                ->sum('payment_amount_received'),
            'collections_30d' => $driver->completedLocations()
                ->where('completed_at', '>=', now()->subDays(30))
                ->sum('payment_amount_received'),
            'avg_time_per_delivery_7d' => $this->calculateAverageDeliveryTime($driver, 7),
            'avg_time_per_delivery_30d' => $this->calculateAverageDeliveryTime($driver, 30),
            'avg_time_per_delivery_all' => $this->calculateAverageDeliveryTime($driver),
            'on_time_rate_7d' => $this->calculateOnTimeRate($driver, 7),
            'on_time_rate_30d' => $this->calculateOnTimeRate($driver, 30),
            'on_time_rate_all' => $this->calculateOnTimeRate($driver),
        ]);

        // Prepare chart data
        $charts = [
            'zone_distribution' => $this->getZoneDistribution($driver),
        ];

        return view('admin.drivers.show', compact('driver', 'stats', 'charts'));
    }

    public function edit(User $driver)
    {
        $zones = Zone::all();
        $driver->load('zones');
        return view('admin.drivers.edit', compact('driver', 'zones'));
    }

    public function update(Request $request, User $driver)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $driver->id,
            'phone_number' => 'required|string|max:20|unique:users,phone_number,' . $driver->id,
            'password' => 'nullable|string|min:8',
            'zones' => 'nullable|array',
            'zones.*' => 'exists:zones,id',
        ]);

        $driver->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
        ]);

        if (!empty($validated['password'])) {
            $driver->update(['password' => bcrypt($validated['password'])]);
        }

        if (isset($validated['zones'])) {
            $driver->zones()->sync($validated['zones']);
        }

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver updated successfully.');
    }

    public function destroy(User $driver)
    {
        $driver->zones()->detach();
        $driver->delete();

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver deleted successfully.');
    }

    public function getActiveHours(Request $request, User $driver)
    {
        $period = $request->query('period', 'month');
        $hours = $this->calculateActiveHours($driver, $period);
        
        return response()->json(['hours' => $hours]);
    }

    private function calculateActiveHours(User $driver, string $period = 'month')
    {
        $query = $driver->completedLocations()->whereNotNull('completed_at');

        // Apply period filter
        switch ($period) {
            case 'day':
                $query->whereDate('completed_at', Carbon::today());
                break;
            case 'week':
                $query->where('completed_at', '>=', Carbon::now()->startOfWeek());
                break;
            case 'month':
                $query->where('completed_at', '>=', Carbon::now()->startOfMonth());
                break;
        }

        $locations = $query->get();
        $totalMinutes = $locations->sum(function ($location) {
            $start = Carbon::parse($location->created_at);
            $end = Carbon::parse($location->completed_at);
            return $end->diffInMinutes($start);
        });

        return round($totalMinutes / 60, 1);
    }

    private function calculateAverageDeliveryTime(User $driver, $days = null)
    {
        $query = $driver->completedLocations();

        if ($days) {
            $query->where('completed_at', '>=', now()->subDays($days));
        }

        $locations = $query->get();

        if ($locations->isEmpty()) {
            return '0h 0m';
        }

        $totalMinutes = $locations->sum(function ($location) {
            $start = Carbon::parse($location->created_at);
            $end = Carbon::parse($location->completed_at);
            return $end->diffInMinutes($start);
        });

        $avgMinutes = round($totalMinutes / $locations->count());
        return sprintf('%dh %dm', floor($avgMinutes / 60), $avgMinutes % 60);
    }

    private function calculateOnTimeRate(User $driver, $days = null)
    {
        $query = $driver->completedLocations();

        if ($days) {
            $query->where('completed_at', '>=', now()->subDays($days));
        }

        $locations = $query->get();
        $total = $locations->count();

        if ($total === 0) return 0;

        $onTime = $locations->filter(function ($location) {
            $start = Carbon::parse($location->created_at);
            $end = Carbon::parse($location->completed_at);
            return $end->diffInMinutes($start) <= 120; // 2 hours threshold
        })->count();

        return ($onTime / $total) * 100;
    }

    private function getZoneDistribution(User $driver)
    {
        $zoneDeliveries = $driver->completedLocations()
            ->with('zone')
            ->get()
            ->groupBy('zone.name')
            ->map(function ($group) {
                return $group->count();
            });

        // Convert Collection to array for array_sum
        $data = $zoneDeliveries->values()->toArray();
        $labels = $zoneDeliveries->keys()->toArray();

        return [
            'labels' => $labels,
            'data' => $data,
            'total' => array_sum($data) // Pre-calculate total for view
        ];
    }
}
