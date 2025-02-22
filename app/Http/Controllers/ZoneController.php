<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ZoneController extends Controller
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
     * Display a listing of the zones.
     */
    public function index()
    {
        $zones = Zone::with('drivers')->get();
        return view('admin.zones.index', compact('zones'));
    }

    /**
     * Show the form for creating a new zone.
     */
    public function create()
    {
        $drivers = User::where('role', 'driver')->get();
        return view('admin.zones.create', compact('drivers'));
    }

    /**
     * Store a newly created zone in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:zones,name',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'drivers' => 'array|nullable',
            'drivers.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();
            
            $zone = Zone::create($validator->validated());

            if ($request->drivers) {
                $zone->drivers()->attach($request->drivers);
            }

            $this->logActivity('create_zone', "Created new zone: {$zone->name}", $request);

            DB::commit();
            return redirect()->route('admin.zones.index')
                ->with('success', 'Zone created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create zone. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified zone.
     */
    public function show(Zone $zone)
    {
        $zone->load(['drivers', 'locations']);
        return view('admin.zones.show', compact('zone'));
    }

    /**
     * Show the form for editing the specified zone.
     */
    public function edit(Zone $zone)
    {
        $drivers = User::where('role', 'driver')->get();
        return view('admin.zones.edit', compact('zone', 'drivers'));
    }

    /**
     * Update the specified zone in storage.
     */
    public function update(Request $request, Zone $zone)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:zones,name,' . $zone->id,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'drivers' => 'array|nullable',
            'drivers.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $zone->update($validator->validated());
            $zone->drivers()->sync($request->drivers ?? []);

            $this->logActivity('update_zone', "Updated zone: {$zone->name}", $request);

            DB::commit();
            return redirect()->route('admin.zones.index')
                ->with('success', 'Zone updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update zone. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified zone from storage.
     */
    public function destroy(Request $request, Zone $zone)
    {
        try {
            DB::beginTransaction();

            $zoneName = $zone->name;
            $zone->delete();

            $this->logActivity('delete_zone', "Deleted zone: {$zoneName}", $request);

            DB::commit();
            return redirect()->route('admin.zones.index')
                ->with('success', 'Zone deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete zone. Please try again.');
        }
    }

    /**
     * Bulk delete zones
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:zones,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $zones = Zone::whereIn('id', $request->ids)->get();
            
            foreach ($zones as $zone) {
                $this->logActivity('delete_zone', "Deleted zone: {$zone->name}", $request);
            }

            Zone::whereIn('id', $request->ids)->delete();

            DB::commit();
            return response()->json(['message' => 'Zones deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete zones'], 500);
        }
    }
}
