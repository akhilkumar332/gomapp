<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\User;
use App\Models\DriverZone;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ZoneController extends Controller
{
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

            // Assign drivers to the zone
            if ($request->drivers) {
                $zone->drivers()->attach($request->drivers);
            }

            // Log the activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'description' => "Created new zone: {$zone->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();
            return redirect()->route('admin.zones.index')->with('success', 'Zone created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create zone. Please try again.')->withInput();
        }
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

            // Update drivers assignment
            $zone->drivers()->sync($request->drivers ?? []);

            // Log the activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'description' => "Updated zone: {$zone->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();
            return redirect()->route('admin.zones.index')->with('success', 'Zone updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update zone. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified zone from storage.
     */
    /**
     * Display the specified zone.
     */
    public function show(Zone $zone)
    {
        $zone->load(['drivers', 'locations']);
        return view('admin.zones.show', compact('zone'));
    }

    public function destroy(Request $request, Zone $zone)
    {
        try {
            DB::beginTransaction();

            // Store zone name for activity log
            $zoneName = $zone->name;

            // Delete the zone
            $zone->delete();

            // Log the activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'description' => "Deleted zone: {$zoneName}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();
            return redirect()->route('admin.zones.index')->with('success', 'Zone deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete zone. Please try again.');
        }
    }
}
