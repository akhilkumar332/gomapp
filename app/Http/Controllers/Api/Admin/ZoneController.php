<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ZoneController extends Controller
{
    /**
     * List all zones
     */
    public function index(Request $request)
    {
        $query = Zone::withCount(['locations', 'drivers']);

        // Apply filters
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $zones = $query->latest()
            ->paginate($request->per_page ?? 15)
            ->through(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'description' => $zone->description,
                    'status' => $zone->status,
                    'boundaries' => $zone->boundaries,
                    'locations_count' => $zone->locations_count,
                    'drivers_count' => $zone->drivers_count,
                    'created_at' => $zone->created_at,
                ];
            });

        return response()->json($zones);
    }

    /**
     * Create a new zone
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:zones',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'boundaries' => 'required|array',
            'boundaries.*.lat' => 'required|numeric|between:-90,90',
            'boundaries.*.lng' => 'required|numeric|between:-180,180',
        ]);

        DB::beginTransaction();

        try {
            $zone = Zone::create([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
                'boundaries' => $request->boundaries,
            ]);

            // Log activity
            ActivityLog::log(
                'zone.create',
                "Created zone {$zone->name}",
                $request->user(),
                $zone
            );

            DB::commit();

            return response()->json([
                'message' => 'Zone created successfully',
                'zone' => [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'description' => $zone->description,
                    'status' => $zone->status,
                    'boundaries' => $zone->boundaries,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update zone details
     */
    public function update(Request $request, Zone $zone)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:zones,name,' . $zone->id,
            'description' => 'nullable|string|max:1000',
            'status' => 'sometimes|required|in:active,inactive',
            'boundaries' => 'sometimes|required|array',
            'boundaries.*.lat' => 'required_with:boundaries|numeric|between:-90,90',
            'boundaries.*.lng' => 'required_with:boundaries|numeric|between:-180,180',
        ]);

        DB::beginTransaction();

        try {
            $zone->update($request->only([
                'name',
                'description',
                'status',
                'boundaries',
            ]));

            // Log activity
            ActivityLog::log(
                'zone.update',
                "Updated zone {$zone->name}",
                $request->user(),
                $zone
            );

            DB::commit();

            return response()->json([
                'message' => 'Zone updated successfully',
                'zone' => [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'description' => $zone->description,
                    'status' => $zone->status,
                    'boundaries' => $zone->boundaries,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a zone
     */
    public function destroy(Request $request, Zone $zone)
    {
        // Check if zone has active locations
        if ($zone->locations()->whereNull('completed_at')->exists()) {
            return response()->json([
                'message' => 'Cannot delete zone with active locations.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Log activity before deletion
            ActivityLog::log(
                'zone.delete',
                "Deleted zone {$zone->name}",
                $request->user(),
                $zone
            );

            // Remove driver assignments
            $zone->drivers()->detach();

            // Delete the zone
            $zone->delete();

            DB::commit();

            return response()->json([
                'message' => 'Zone deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get zone statistics
     */
    public function statistics(Request $request, Zone $zone)
    {
        $stats = [
            'total_locations' => $zone->locations()->count(),
            'active_locations' => $zone->locations()->whereNull('completed_at')->count(),
            'completed_locations' => $zone->locations()->whereNotNull('completed_at')->count(),
            'total_drivers' => $zone->drivers()->count(),
            'active_drivers' => $zone->drivers()
                ->whereNotNull('last_location_update')
                ->where('last_location_update', '>=', now()->subHour())
                ->count(),
            'total_collections' => $zone->locations()
                ->where('status', 'completed')
                ->where('payment_received', true)
                ->sum('payment_amount_received'),
            'today_collections' => $zone->locations()
                ->whereDate('completed_at', today())
                ->where('status', 'completed')
                ->where('payment_received', true)
                ->sum('payment_amount_received'),
        ];

        return response()->json(['statistics' => $stats]);
    }
}
