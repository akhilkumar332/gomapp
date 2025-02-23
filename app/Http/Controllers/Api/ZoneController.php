<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    /**
     * Get all zones assigned to the authenticated driver
     */
    public function index(Request $request)
    {
        $zones = $request->user()->zones()
            ->with(['locations' => function ($query) {
                $query->whereNull('completed_at')
                    ->orderBy('priority', 'desc');
            }])
            ->get();

        return response()->json([
            'zones' => $zones->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'boundaries' => $zone->boundaries,
                    'active_locations_count' => $zone->locations->count(),
                ];
            })
        ]);
    }

    /**
     * Get a specific zone with its locations
     */
    public function show(Request $request, Zone $zone)
    {
        // Check if driver is assigned to this zone
        if (!$request->user()->zones()->where('id', $zone->id)->exists()) {
            return response()->json([
                'message' => 'You are not assigned to this zone.'
            ], 403);
        }

        $zone->load(['locations' => function ($query) {
            $query->whereNull('completed_at')
                ->orderBy('priority', 'desc');
        }]);

        return response()->json([
            'zone' => [
                'id' => $zone->id,
                'name' => $zone->name,
                'boundaries' => $zone->boundaries,
                'locations' => $zone->locations->map(function ($location) {
                    return [
                        'id' => $location->id,
                        'address' => $location->address,
                        'digital_address' => $location->digital_address,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                        'status' => $location->status,
                        'priority' => $location->priority,
                        'payment_required' => $location->payment_required,
                        'payment_amount' => $location->payment_amount,
                        'payment_method' => $location->payment_method,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Get locations for a specific zone
     */
    public function locations(Request $request, Zone $zone)
    {
        // Check if driver is assigned to this zone
        if (!$request->user()->zones()->where('id', $zone->id)->exists()) {
            return response()->json([
                'message' => 'You are not assigned to this zone.'
            ], 403);
        }

        $locations = $zone->locations()
            ->whereNull('completed_at')
            ->orderBy('priority', 'desc')
            ->get()
            ->map(function ($location) {
                return [
                    'id' => $location->id,
                    'address' => $location->address,
                    'digital_address' => $location->digital_address,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'status' => $location->status,
                    'priority' => $location->priority,
                    'payment_required' => $location->payment_required,
                    'payment_amount' => $location->payment_amount,
                    'payment_method' => $location->payment_method,
                ];
            });

        return response()->json(['locations' => $locations]);
    }
}
