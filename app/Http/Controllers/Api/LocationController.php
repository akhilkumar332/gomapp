<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    /**
     * Get all locations assigned to the authenticated driver's zones
     */
    public function index(Request $request)
    {
        $locations = Location::whereIn('zone_id', $request->user()->zones->pluck('id'))
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
                    'zone' => [
                        'id' => $location->zone->id,
                        'name' => $location->zone->name,
                    ],
                ];
            });

        return response()->json(['locations' => $locations]);
    }

    /**
     * Get a specific location
     */
    public function show(Request $request, Location $location)
    {
        // Check if driver is assigned to this location's zone
        if (!$request->user()->zones()->where('id', $location->zone_id)->exists()) {
            return response()->json([
                'message' => 'You are not assigned to this location\'s zone.'
            ], 403);
        }

        return response()->json([
            'location' => [
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
                'zone' => [
                    'id' => $location->zone->id,
                    'name' => $location->zone->name,
                ],
            ]
        ]);
    }

    /**
     * Update location status
     */
    public function updateStatus(Request $request, Location $location)
    {
        // Check if driver is assigned to this location's zone
        if (!$request->user()->zones()->where('id', $location->zone_id)->exists()) {
            return response()->json([
                'message' => 'You are not assigned to this location\'s zone.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,completed,failed',
            'notes' => 'nullable|string|max:500',
            'payment_received' => 'required_if:status,completed|boolean',
            'payment_amount_received' => 'required_if:payment_received,true|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location->update([
            'status' => $request->status,
            'notes' => $request->notes,
            'completed_at' => in_array($request->status, ['completed', 'failed']) ? now() : null,
            'payment_received' => $request->payment_received ?? false,
            'payment_amount_received' => $request->payment_amount_received,
            'completed_by' => in_array($request->status, ['completed', 'failed']) ? $request->user()->id : null,
        ]);

        // Log activity
        ActivityLog::log(
            'location.status_update',
            "Updated location {$location->id} status to {$request->status}",
            $request->user(),
            $location
        );

        return response()->json([
            'message' => 'Location status updated successfully',
            'location' => [
                'id' => $location->id,
                'status' => $location->status,
                'completed_at' => $location->completed_at,
            ]
        ]);
    }

    /**
     * Update driver's current position
     */
    public function updatePosition(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $request->user()->update([
            'last_latitude' => $request->latitude,
            'last_longitude' => $request->longitude,
            'last_location_update' => now(),
        ]);

        return response()->json([
            'message' => 'Position updated successfully'
        ]);
    }
}
