<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\Location;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    /**
     * Display a listing of the locations.
     */
    public function index(Request $request)
    {
        $query = Location::with(['zone']);

        // Apply filters
        if ($request->zone_id) {
            $query->where('zone_id', $request->zone_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('shop_name', 'like', "%{$request->search}%")
                  ->orWhere('address', 'like', "%{$request->search}%")
                  ->orWhere('ghana_post_gps_code', 'like', "%{$request->search}%");
            });
        }

        $locations = $query->paginate(10);
        $zones = Zone::where('status', 'active')->get();

        return view('admin.locations.index', compact('locations', 'zones'));
    }

    /**
     * Show the form for creating a new location.
     */
    public function create()
    {
        $zones = Zone::where('status', 'active')->get();
        return view('admin.locations.create', compact('zones'));
    }

    /**
     * Store a newly created location in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|exists:zones,id',
            'shop_name' => 'required|string|max:255',
            'address' => 'required|string',
            'ghana_post_gps_code' => 'required|string|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'contact_number' => 'nullable|string|max:15',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $location = Location::create($validator->validated());

            // Log the activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'description' => "Created new location: {$location->shop_name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();
            return redirect()->route('admin.locations.index')
                ->with('success', 'Location created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create location. Please try again.')->withInput();
        }
    }

    /**
     * Show the form for editing the specified location.
     */
    public function edit(Location $location)
    {
        $zones = Zone::where('status', 'active')->get();
        return view('admin.locations.edit', compact('location', 'zones'));
    }

    /**
     * Update the specified location in storage.
     */
    public function update(Request $request, Location $location)
    {
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|exists:zones,id',
            'shop_name' => 'required|string|max:255',
            'address' => 'required|string',
            'ghana_post_gps_code' => 'required|string|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'contact_number' => 'nullable|string|max:15',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $location->update($validator->validated());

            // Log the activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'description' => "Updated location: {$location->shop_name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();
            return redirect()->route('admin.locations.index')
                ->with('success', 'Location updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update location. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified location from storage.
     */
    public function destroy(Request $request, Location $location)
    {
        try {
            DB::beginTransaction();

            // Store location name for activity log
            $locationName = $location->shop_name;

            // Delete the location
            $location->delete();

            // Log the activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'description' => "Deleted location: {$locationName}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();
            return redirect()->route('admin.locations.index')
                ->with('success', 'Location deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete location. Please try again.');
        }
    }

    /**
     * Get locations for a specific zone (API endpoint for drivers).
     */
    public function getZoneLocations(Zone $zone)
    {
        // Check if the authenticated driver is assigned to this zone
        if (auth()->user()->isDriver() && !auth()->user()->zones->contains($zone->id)) {
            return response()->json(['message' => 'Unauthorized access to zone'], 403);
        }

        $locations = $zone->locations()
            ->where('status', 'active')
            ->get()
            ->map(function ($location) {
                return [
                    'id' => $location->id,
                    'shop_name' => $location->shop_name,
                    'address' => $location->address,
                    'ghana_post_gps_code' => $location->ghana_post_gps_code,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'contact_number' => $location->contact_number
                ];
            });

        return response()->json($locations);
    }
}
