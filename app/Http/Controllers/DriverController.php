<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Zone;
use App\Models\Location;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = User::where('role', 'driver')
                ->with(['zones' => function($query) {
                    $query->withCount([
                        'locations',
                        'locations as active_locations_count' => function($query) {
                            $query->whereNull('completed_at');
                        }
                    ]);
                }, 'completedLocations' => function($query) {
                    $query->select('id', 'completed_by', 'completed_at', 'payment_received', 'payment_amount_received')
                        ->whereNotNull('completed_at')
                        ->latest('completed_at')
                        ->take(5);
                }]);

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

            $drivers = $query->latest()->paginate(10);

            foreach ($drivers as $driver) {
                $driver->performance = $this->calculateDriverPerformance($driver);
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Viewed drivers list',
                'description' => 'Admin viewed the drivers list with filters: ' . json_encode($request->all()),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'html' => view('admin.drivers.partials.driver-list', compact('drivers'))->render()
                ]);
            }

            return view('admin.drivers.index', compact('drivers'));
        } catch (\Exception $e) {
            Log::error('Error in driver index: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'An error occurred while loading drivers.',
                    'html' => view('admin.drivers.partials.error')->render()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'An error occurred while loading drivers. Please try again.');
        }
    }

    public function create()
    {
        try {
            $zones = Zone::withCount([
                'locations',
                'locations as active_locations_count' => function($query) {
                    $query->whereNull('completed_at');
                }
            ])->orderBy('name')->get();

            return view('admin.drivers.create', compact('zones'));
        } catch (\Exception $e) {
            Log::error('Error in driver create: ' . $e->getMessage());
            return redirect()->route('admin.drivers.index')
                ->with('error', 'Error loading create driver form. Please try again.');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone_number' => 'required|string|max:20|unique:users,phone_number',
                'password' => 'required|string|min:8',
                'status' => 'required|in:active,inactive,suspended',
                'zones' => 'nullable|array',
                'zones.*' => 'exists:zones,id',
            ]);

            DB::beginTransaction();

            $phone = $validated['phone_number'];
            if (!str_starts_with($phone, '+233')) {
                $phone = '+233' . ltrim($phone, '0');
            }

            $driver = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone_number' => $phone,
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'],
                'role' => 'driver',
            ]);

            if (!empty($validated['zones'])) {
                $driver->zones()->attach($validated['zones']);
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Created driver',
                'description' => "Created new driver: {$driver->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route('admin.drivers.index')
                ->with('success', 'Driver created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating driver: ' . $e->getMessage(), [
                'request' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating driver. Please try again.');
        }
    }

    public function show(User $driver)
    {
        try {
            $driver->load([
                'zones' => function($query) {
                    $query->withCount([
                        'locations',
                        'locations as active_locations_count' => function($query) {
                            $query->whereNull('completed_at');
                        },
                        'locations as completed_locations_count' => function($query) {
                            $query->whereNotNull('completed_at');
                        }
                    ]);
                },
                'completedLocations' => function($query) {
                    $query->with(['zone' => function($query) {
                        $query->withCount([
                            'locations',
                            'locations as active_locations_count' => function($query) {
                                $query->whereNull('completed_at');
                            }
                        ]);
                    }])
                    ->whereNotNull('completed_at')
                    ->latest('completed_at');
                }
            ]);

            $stats = $this->calculateBasicStats($driver);
            $stats = array_merge($stats, $this->calculatePeriodStats($driver));
            $stats['deliveries_7d_daily'] = $this->calculateDailyDeliveries($driver);

            $charts = [
                'zone_distribution' => $this->getZoneDistribution($driver),
            ];

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Viewed driver details',
                'description' => "Viewed details for driver: {$driver->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return view('admin.drivers.show', compact('driver', 'stats', 'charts'));
        } catch (\Exception $e) {
            Log::error('Error showing driver details: ' . $e->getMessage(), [
                'driver_id' => $driver->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.drivers.index')
                ->with('error', 'Error loading driver details. Please try again.');
        }
    }

    public function edit(User $driver)
    {
        try {
            $zones = Zone::withCount([
                'locations',
                'locations as active_locations_count' => function($query) {
                    $query->whereNull('completed_at');
                }
            ])->orderBy('name')->get();

            $driver->load(['zones' => function($query) {
                $query->withCount([
                    'locations',
                    'locations as active_locations_count' => function($query) {
                        $query->whereNull('completed_at');
                    }
                ]);
            }]);

            return view('admin.drivers.edit', compact('driver', 'zones'));
        } catch (\Exception $e) {
            Log::error('Error in driver edit: ' . $e->getMessage(), [
                'driver_id' => $driver->id
            ]);
            return redirect()->route('admin.drivers.index')
                ->with('error', 'Error loading edit driver form. Please try again.');
        }
    }

    public function update(Request $request, User $driver)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $driver->id,
                'phone_number' => 'required|string|max:20|unique:users,phone_number,' . $driver->id,
                'password' => 'nullable|string|min:8',
                'status' => 'required|in:active,inactive,suspended',
                'zones' => 'nullable|array',
                'zones.*' => 'exists:zones,id',
            ]);

            DB::beginTransaction();

            $phone = $validated['phone_number'];
            if (!str_starts_with($phone, '+233')) {
                $phone = '+233' . ltrim($phone, '0');
            }

            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone_number' => $phone,
                'status' => $validated['status'],
            ];

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $driver->update($updateData);

            if (isset($validated['zones'])) {
                $driver->zones()->sync($validated['zones']);
            }

            if ($validated['status'] === 'suspended') {
                Location::where('assigned_to', $driver->id)
                    ->whereNull('completed_at')
                    ->update(['assigned_to' => null]);
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Updated driver',
                'description' => "Updated driver: {$driver->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route('admin.drivers.index')
                ->with('success', 'Driver updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating driver: ' . $e->getMessage(), [
                'driver_id' => $driver->id,
                'request' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating driver. Please try again.');
        }
    }

    public function destroy(User $driver)
    {
        try {
            DB::beginTransaction();
            
            $hasActiveDeliveries = Location::where('assigned_to', $driver->id)
                ->whereNull('completed_at')
                ->exists();

            if ($hasActiveDeliveries) {
                return redirect()->route('admin.drivers.index')
                    ->with('error', 'Cannot delete driver with active deliveries.');
            }

            Location::where('assigned_to', $driver->id)
                ->whereNull('completed_at')
                ->update(['assigned_to' => null]);

            $driver->zones()->detach();

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Deleted driver',
                'description' => "Deleted driver: {$driver->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            $driver->delete();
            
            DB::commit();

            return redirect()->route('admin.drivers.index')
                ->with('success', 'Driver deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting driver: ' . $e->getMessage(), [
                'driver_id' => $driver->id
            ]);
            
            return redirect()->route('admin.drivers.index')
                ->with('error', 'Error deleting driver. Please try again.');
        }
    }

    public function getActiveHours(Request $request, User $driver)
    {
        try {
            $period = $request->query('period', 'month');
            $hours = $this->calculateActiveHours($driver, $period);
            
            return response()->json(['hours' => $hours]);
        } catch (\Exception $e) {
            Log::error('Error calculating active hours: ' . $e->getMessage(), [
                'driver_id' => $driver->id,
                'period' => $period
            ]);
            return response()->json(['error' => 'Error calculating active hours'], 500);
        }
    }

    private function calculateBasicStats(User $driver)
    {
        try {
            $completedLocations = $driver->completedLocations()
                ->whereNotNull('completed_at')
                ->get();

            return [
                'total_deliveries' => $completedLocations->count(),
                'total_collections' => $completedLocations->sum('payment_amount_received'),
                'avg_deliveries_per_day' => $this->calculateAverageDeliveriesPerDay($driver),
                'active_hours' => $this->calculateActiveHours($driver, 'month'),
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating basic stats: ' . $e->getMessage(), [
                'driver_id' => $driver->id
            ]);
            return [
                'total_deliveries' => 0,
                'total_collections' => 0,
                'avg_deliveries_per_day' => 0,
                'active_hours' => 0,
            ];
        }
    }

    private function calculateAverageDeliveriesPerDay(User $driver)
    {
        try {
            $thirtyDaysAgo = now()->subDays(30)->startOfDay();
            $completedCount = $driver->completedLocations()
                ->where('completed_at', '>=', $thirtyDaysAgo)
                ->count();

            $firstDelivery = $driver->completedLocations()
                ->oldest('completed_at')
                ->first();

            if (!$firstDelivery) {
                return 0;
            }

            $daysActive = max(1, $firstDelivery->completed_at->diffInDays(now()));
            $daysActive = min($daysActive, 30);

            return round($completedCount / $daysActive, 1);
        } catch (\Exception $e) {
            Log::error('Error calculating average deliveries per day: ' . $e->getMessage());
            return 0;
        }
    }

    private function calculatePeriodStats(User $driver)
    {
        try {
            $now = now();
            $sevenDaysAgo = $now->copy()->subDays(7)->startOfDay();
            $thirtyDaysAgo = $now->copy()->subDays(30)->startOfDay();

            $completedLocations = $driver->completedLocations()
                ->where('completed_at', '>=', $thirtyDaysAgo)
                ->get();

            $last7Days = $completedLocations->where('completed_at', '>=', $sevenDaysAgo);
            $last30Days = $completedLocations;

            return [
                'deliveries_7d' => $last7Days->count(),
                'deliveries_30d' => $last30Days->count(),
                'collections_7d' => $last7Days->sum('payment_amount_received'),
                'collections_30d' => $last30Days->sum('payment_amount_received'),
                'avg_time_per_delivery_7d' => $this->calculateAverageDeliveryTime($last7Days),
                'avg_time_per_delivery_30d' => $this->calculateAverageDeliveryTime($last30Days),
                'on_time_rate_7d' => $this->calculateOnTimeRate($last7Days),
                'on_time_rate_30d' => $this->calculateOnTimeRate($last30Days),
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating period stats: ' . $e->getMessage());
            return [
                'deliveries_7d' => 0,
                'deliveries_30d' => 0,
                'collections_7d' => 0,
                'collections_30d' => 0,
                'avg_time_per_delivery_7d' => '0h 0m',
                'avg_time_per_delivery_30d' => '0h 0m',
                'on_time_rate_7d' => 0,
                'on_time_rate_30d' => 0,
            ];
        }
    }

    private function calculateDailyDeliveries(User $driver)
    {
        try {
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();

            $dailyDeliveries = $driver->completedLocations()
                ->whereBetween('completed_at', [$startOfWeek, $endOfWeek])
                ->selectRaw('DATE(completed_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();

            $result = [];
            for ($i = 0; $i < 7; $i++) {
                $date = $startOfWeek->copy()->addDays($i)->format('Y-m-d');
                $result[] = $dailyDeliveries[$date] ?? 0;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Error calculating daily deliveries: ' . $e->getMessage());
            return array_fill(0, 7, 0);
        }
    }

    private function calculateDriverPerformance(User $driver)
    {
        try {
            $startDate = now()->subDays(30)->startOfDay();
            $endDate = now()->endOfDay();

            $completedLocations = $driver->completedLocations()
                ->whereBetween('completed_at', [$startDate, $endDate])
                ->count();

            $totalAssignedLocations = Location::where(function($query) use ($driver) {
                    $query->where('assigned_to', $driver->id)
                        ->orWhere('completed_by', $driver->id);
                })
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->count();

            $onTimeDeliveries = $driver->completedLocations()
                ->whereBetween('completed_at', [$startDate, $endDate])
                ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, completed_at) <= 2')
                ->count();

            $successRate = $totalAssignedLocations > 0 
                ? ($completedLocations / $totalAssignedLocations) * 100 
                : 0;

            $onTimeRate = $completedLocations > 0 
                ? ($onTimeDeliveries / $completedLocations) * 100 
                : 0;

            return [
                'success_rate' => round($successRate, 1),
                'completed_count' => $completedLocations,
                'total_count' => $totalAssignedLocations,
                'on_time_rate' => round($onTimeRate, 1),
                'period' => '30d'
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating driver performance', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success_rate' => 0,
                'completed_count' => 0,
                'total_count' => 0,
                'on_time_rate' => 0,
                'period' => '30d'
            ];
        }
    }

    private function calculateActiveHours(User $driver, string $period = 'month')
    {
        try {
            $query = $driver->completedLocations()->whereNotNull('completed_at');

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
        } catch (\Exception $e) {
            Log::error('Error calculating active hours: ' . $e->getMessage());
            return 0;
        }
    }

    private function calculateAverageDeliveryTime($locations)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Error calculating average delivery time: ' . $e->getMessage());
            return '0h 0m';
        }
    }

    private function calculateOnTimeRate($locations)
    {
        try {
            if ($locations->isEmpty()) {
                return 0;
            }

            $onTime = $locations->filter(function ($location) {
                $start = Carbon::parse($location->created_at);
                $end = Carbon::parse($location->completed_at);
                return $end->diffInMinutes($start) <= 120; // 2 hours threshold
            })->count();

            return ($onTime / $locations->count()) * 100;
        } catch (\Exception $e) {
            Log::error('Error calculating on-time rate: ' . $e->getMessage());
            return 0;
        }
    }

    private function getZoneDistribution(User $driver)
    {
        try {
            $zoneDeliveries = $driver->completedLocations()
                ->with('zone')
                ->select('zone_id', DB::raw('COUNT(*) as count'))
                ->whereNotNull('zone_id')
                ->groupBy('zone_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->zone->name => $item->count];
                });

            $data = $zoneDeliveries->values()->toArray();
            $labels = $zoneDeliveries->keys()->toArray();
            $total = array_sum($data);

            return [
                'labels' => $labels,
                'data' => $data,
                'total' => $total
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating zone distribution: ' . $e->getMessage());
            return [
                'labels' => [],
                'data' => [],
                'total' => 0
            ];
        }
    }
}
