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
     * Create activity log entry
     */
    private function logActivity(string $action, string $description, Request $request): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'device_type' => ActivityLog::getDeviceType($request->userAgent()),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
    }

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

        try {
            DB::beginTransaction();

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

            $this->logActivity('create_driver', "Created new driver: {$driver->name}", $request);

            DB::commit();
            return redirect()->route('admin.drivers.index')
                ->with('success', 'Driver created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create driver. Please try again.')
                ->withInput();
        }
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

        try {
            DB::beginTransaction();

            $driver->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number']
            ]);

            if (!empty($validated['password'])) {
                $driver->update(['password' => bcrypt($validated['password'])]);
            }

            $driver->zones()->sync($validated['zones'] ?? []);

            $this->logActivity('update_driver', "Updated driver: {$driver->name}", $request);

            DB::commit();
            return redirect()->route('admin.drivers.index')
                ->with('success', 'Driver updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update driver. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified driver from storage.
     */
    public function destroy(Request $request, User $driver)
    {
        if ($driver->role !== 'driver') {
            abort(404);
        }

        try {
            DB::beginTransaction();

            $driverName = $driver->name;
            $driver->delete();

            $this->logActivity('delete_driver', "Deleted driver: {$driverName}", $request);

            DB::commit();
            return redirect()->route('admin.drivers.index')
                ->with('success', 'Driver deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete driver. Please try again.');
        }
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

        try {
            DB::beginTransaction();

            $drivers = User::whereIn('id', $validated['ids'])
                ->where('role', 'driver')
                ->get();

            foreach ($drivers as $driver) {
                $this->logActivity('delete_driver', "Deleted driver: {$driver->name}", $request);
            }

            User::whereIn('id', $validated['ids'])
                ->where('role', 'driver')
                ->delete();

            DB::commit();
            return response()->json(['message' => 'Drivers deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete drivers'], 500);
        }
    }

    /**
     * Update driver's online status.
     */
    public function updateStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            $driver = Auth::user();
            $driver->is_online = $request->is_online;
            $driver->save();

            $this->logActivity(
                'status_change',
                'Driver went ' . ($request->is_online ? 'online' : 'offline'),
                $request
            );

            DB::commit();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update status'], 500);
        }
    }

    // Rest of your existing methods...
}
