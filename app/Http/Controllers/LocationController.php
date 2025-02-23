<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Zone;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::with(['zone', 'completedBy'])
            ->latest()
            ->paginate(10);

        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        $zones = Zone::where('status', 'active')->get();
        return view('admin.locations.create', compact('zones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'shop_name' => 'required|string|max:255',
            'address' => 'required|string',
            'ghana_post_gps_code' => 'required|string|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'contact_number' => 'required|string|max:20',
            'status' => 'required|in:active,inactive',
            'priority' => 'required|integer|min:1|max:5',
        ]);

        Location::create($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location created successfully.');
    }

    public function show(Location $location)
    {
        $location->load(['zone', 'completedBy']);
        
        return view('admin.locations.show', compact('location'));
    }

    public function edit(Location $location)
    {
        $zones = Zone::where('status', 'active')->get();
        return view('admin.locations.edit', compact('location', 'zones'));
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'shop_name' => 'required|string|max:255',
            'address' => 'required|string',
            'ghana_post_gps_code' => 'required|string|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'contact_number' => 'required|string|max:20',
            'status' => 'required|in:active,inactive',
            'priority' => 'required|integer|min:1|max:5',
        ]);

        $location->update($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location deleted successfully.');
    }
}
