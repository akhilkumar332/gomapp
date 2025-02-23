<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\ActivityLog;
use App\Services\GhanaPostGPSService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    protected $ghanaPostGPSService;

    public function __construct(GhanaPostGPSService $ghanaPostGPSService)
    {
        $this->ghanaPostGPSService = $ghanaPostGPSService;
    }

    /**
     * List all locations
     */
    public function index(Request $request)
    {
        $query = Location::with(['zone:id,name']);

        // Apply filters
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('shop_name', 'like', "%{$request->search}%")
                    ->orWhere('address', 'like', "%{$request->search}%")
                    ->orWhere('digital_address', 'like', "%{$request->search}%");
            });
        }

        if ($request->zone_id) {
            $query->where('zone_id', $request->zone_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->completed) {
            $query->whereNotNull('completed_at');
        }

        $locations = $query->latest()
            ->paginate($request->per_page ?? 15)
            ->through(function ($location) {
                return [
                    'id' => $location->id,
                    'zone' => [
                        'id' => $location->zone->id,
                        'name' => $location->zone->name,
                    ],
                    'shop_name' => $location->shop_name,
                    'address' => $location->address,
                    'digital_address' => $location->digital_address,
                    'coordinates' => [
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                    ],
                    'status' => $location->status,
                    'priority' => $location->priority,
                    'payment_required' => $location->payment_required,
                    'payment_amount' => $location->payment_amount,
                    'payment_method' => $location->payment_method,
                    'completed_at' => $location->completed_at,
                    'created_at' => $location->created_at,
                ];
            });

        return response()->json($locations);
    }

    /**
     * Create a new location
     */
    public function store(Request $request)
    {
        $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'shop_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'digital_address' => 'required|string|max:50',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'priority' => 'nullable|integer|min:0',
            'payment_required' => 'required|boolean',
            'payment_amount' => 'required_if:payment_required,true|numeric|min:0',
            'payment_method' => 'required_if:payment_required,true|string|in:cash,mobile_money,card',
        ]);

        // Verify digital address with Ghana Post GPS
        $gpsData = $this->ghanaPostGPSService->verifyAddress($request->digital_address);
        if (!$gpsData['valid']) {
            return response()->json([
                'message' => 'Invalid Ghana Post GPS address.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $location = Location::create([
                'zone_id' => $request->zone_id,
                'shop_name' => $request->shop_name,
                'address' => $request->address,
                'digital_address' => $request->digital_address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'priority' => $request->priority ?? 0,
                'payment_required' => $request->payment_required,
                'payment_amount' => $request->payment_amount,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
            ]);

            // Log activity
            ActivityLog::log(
                'location.create',
                "Created location for {$location->shop_name}",
                $request->user(),
                $location
            );

            DB::commit();

            return response()->json([
                'message' => 'Location created successfully',
                'location' => [
                    'id' => $location->id,
                    'shop_name' => $location->shop_name,
                    'address' => $location->address,
                    'digital_address' => $location->digital_address,
                    'coordinates' => [
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update location details
     */
    public function update(Request $request, Location $location)
    {
        $request->validate([
            'zone_id' => 'sometimes|required|exists:zones,id',
            'shop_name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:500',
            'digital_address' => 'sometimes|required|string|max:50',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'priority' => 'nullable|integer|min:0',
            'payment_required' => 'sometimes|required|boolean',
            'payment_amount' => 'required_if:payment_required,true|numeric|min:0',
            'payment_method' => 'required_if:payment_required,true|string|in:cash,mobile_money,card',
            'status' => 'sometimes|required|in:pending,in_progress,completed,failed',
        ]);

        // Verify digital address if provided
        if ($request->has('digital_address')) {
            $gpsData = $this->ghanaPostGPSService->verifyAddress($request->digital_address);
            if (!$gpsData['valid']) {
                return response()->json([
                    'message' => 'Invalid Ghana Post GPS address.'
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            $location->update($request->only([
                'zone_id',
                'shop_name',
                'address',
                'digital_address',
                'latitude',
                'longitude',
                'priority',
                'payment_required',
                'payment_amount',
                'payment_method',
                'status',
            ]));

            // Log activity
            ActivityLog::log(
                'location.update',
                "Updated location for {$location->shop_name}",
                $request->user(),
                $location
            );

            DB::commit();

            return response()->json([
                'message' => 'Location updated successfully',
                'location' => [
                    'id' => $location->id,
                    'shop_name' => $location->shop_name,
                    'address' => $location->address,
                    'digital_address' => $location->digital_address,
                    'coordinates' => [
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a location
     */
    public function destroy(Request $request, Location $location)
    {
        if ($location->status === 'in_progress') {
            return response()->json([
                'message' => 'Cannot delete location that is in progress.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Log activity before deletion
            ActivityLog::log(
                'location.delete',
                "Deleted location for {$location->shop_name}",
                $request->user(),
                $location
            );

            // Delete the location
            $location->delete();

            DB::commit();

            return response()->json([
                'message' => 'Location deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
