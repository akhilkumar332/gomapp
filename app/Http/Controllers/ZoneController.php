<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index()
    {
        $zones = Zone::withCount(['locations', 'drivers']) // Ensure drivers relationship is used
            ->latest()
            ->paginate(10);

        return view('admin.zones.index', compact('zones'));
    }

    public function create()
    {
        return view('admin.zones.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'center_lat' => 'required|numeric',
            'center_lng' => 'required|numeric',
            'radius' => 'required|integer',
            'status' => 'required|in:active,inactive',
        ]);

        Zone::create($validated);

        return redirect()->route('admin.zones.index')
            ->with('success', 'Zone created successfully.');
    }

    public function show(Zone $zone)
    {
        $zone->load(['locations', 'drivers']);
        
        return view('admin.zones.show', compact('zone'));
    }

    public function edit(Zone $zone)
    {
        return view('admin.zones.edit', compact('zone'));
    }

    public function update(Request $request, Zone $zone)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'center_lat' => 'required|numeric',
            'center_lng' => 'required|numeric',
            'radius' => 'required|integer',
            'status' => 'required|in:active,inactive',
        ]);

        $zone->update($validated);

        return redirect()->route('admin.zones.index')
            ->with('success', 'Zone updated successfully.');
    }

    public function destroy(Zone $zone)
    {
        $zone->delete();

        return redirect()->route('admin.zones.index')
            ->with('success', 'Zone deleted successfully.');
    }
}
