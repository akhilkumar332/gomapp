<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\DriverZone;
use Illuminate\Http\Request;
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'drivers' => 'array|nullable',
            'drivers.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $zone = Zone::create($validator->validated());

        // Assign drivers to the zone
        if ($request->drivers) {
            $zone->drivers()->attach($request->drivers);
        }

        return redirect()->route('admin.zones.index')->with('success', 'Zone created successfully.');
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'drivers' => 'array|nullable',
            'drivers.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $zone->update($validator->validated());

        // Update drivers assignment
        if ($request->drivers) {
            $zone->drivers()->sync($request->drivers);
        }

        return redirect()->route('admin.zones.index')->with('success', 'Zone updated successfully.');
    }

    /**
     * Remove the specified zone from storage.
     */
    public function destroy(Zone $zone)
    {
        $zone->delete();
        return redirect()->route('admin.zones.index')->with('success', 'Zone deleted successfully.');
    }
}
