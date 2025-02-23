<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class DriverController extends Controller
{
    /**
     * List all drivers
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'driver')
            ->with(['zones' => function ($query) {
                $query->select('zones.id', 'name');
            }]);

        // Apply filters
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('phone_number', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $drivers = $query->latest()
            ->paginate($request->per_page ?? 15)
            ->through(function ($driver) {
                return [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'email' => $driver->email,
                    'phone_number' => $driver->phone_number,
                    'status' => $driver->status,
                    'phone_verified' => $driver->phone_verified,
                    'last_location_update' => $driver->last_location_update,
                    'assigned_zones' => $driver->zones->map(function ($zone) {
                        return [
                            'id' => $zone->id,
                            'name' => $zone->name,
                        ];
                    }),
                    'created_at' => $driver->created_at,
                ];
            });

        return response()->json($drivers);
    }

    /**
     * Create a new driver
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20|unique:users',
            'password' => ['required', Password::defaults()],
            'zone_ids' => 'required|array',
            'zone_ids.*' => 'exists:zones,id',
        ]);

        DB::beginTransaction();

        try {
            $driver = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
                'role' => 'driver',
                'status' => 'active',
            ]);

            // Assign zones
            $driver->zones()->attach($request->zone_ids);

            // Log activity
            ActivityLog::log(
                'driver.create',
                "Created driver account for {$driver->name}",
                $request->user(),
                $driver
            );

            DB::commit();

            return response()->json([
                'message' => 'Driver created successfully',
                'driver' => [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'email' => $driver->email,
                    'phone_number' => $driver->phone_number,
                    'status' => $driver->status,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update driver details
     */
    public function update(Request $request, User $driver)
    {
        if ($driver->role !== 'driver') {
            return response()->json([
                'message' => 'This user is not a driver.'
            ], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $driver->id,
            'phone_number' => 'sometimes|required|string|max:20|unique:users,phone_number,' . $driver->id,
            'password' => ['sometimes', 'required', Password::defaults()],
            'status' => 'sometimes|required|in:active,inactive',
            'zone_ids' => 'sometimes|required|array',
            'zone_ids.*' => 'exists:zones,id',
        ]);

        DB::beginTransaction();

        try {
            $updates = $request->only(['name', 'email', 'phone_number', 'status']);
            if ($request->has('password')) {
                $updates['password'] = Hash::make($request->password);
            }

            $driver->update($updates);

            // Update zone assignments if provided
            if ($request->has('zone_ids')) {
                $driver->zones()->sync($request->zone_ids);
            }

            // Log activity
            ActivityLog::log(
                'driver.update',
                "Updated driver account for {$driver->name}",
                $request->user(),
                $driver
            );

            DB::commit();

            return response()->json([
                'message' => 'Driver updated successfully',
                'driver' => [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'email' => $driver->email,
                    'phone_number' => $driver->phone_number,
                    'status' => $driver->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a driver
     */
    public function destroy(Request $request, User $driver)
    {
        if ($driver->role !== 'driver') {
            return response()->json([
                'message' => 'This user is not a driver.'
            ], 403);
        }

        DB::beginTransaction();

        try {
            // Log activity before deletion
            ActivityLog::log(
                'driver.delete',
                "Deleted driver account for {$driver->name}",
                $request->user(),
                $driver
            );

            // Remove zone assignments
            $driver->zones()->detach();

            // Soft delete the driver
            $driver->delete();

            DB::commit();

            return response()->json([
                'message' => 'Driver deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
