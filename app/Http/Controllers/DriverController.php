<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'driver')->with(['zones']);

        // Apply filters
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('phone')) {
            $query->where('phone_number', 'like', '%' . $request->phone . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $drivers = $query->latest()->paginate(10)->withQueryString();

        return view('admin.drivers.index', compact('drivers'));
    }

    public function create()
    {
        $zones = Zone::all();
        return view('admin.drivers.create', compact('zones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
            'password' => 'required|string|min:8',
            'status' => 'required|in:active,inactive',
            'zones' => 'nullable|array',
            'zones.*' => 'exists:zones,id',
        ]);

        // Format phone number to include Ghana prefix if not present
        $phone = $validated['phone_number'];
        if (!str_starts_with($phone, '+233')) {
            $phone = '+233' . ltrim($phone, '0');
        }

        $driver = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $phone,
            'password' => bcrypt($validated['password']),
            'status' => $validated['status'],
            'role' => 'driver',
        ]);

        if (!empty($validated['zones'])) {
            $driver->zones()->attach($validated['zones']);
        }

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver created successfully.');
    }

    public function show(User $driver)
    {
        $driver->load('zones');
        return view('admin.drivers.show', compact('driver'));
    }

    public function edit(User $driver)
    {
        $zones = Zone::all();
        $driver->load('zones');
        return view('admin.drivers.edit', compact('driver', 'zones'));
    }

    public function update(Request $request, User $driver)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $driver->id,
            'phone_number' => 'required|string|max:20|unique:users,phone_number,' . $driver->id,
            'password' => 'nullable|string|min:8',
            'zones' => 'nullable|array',
            'zones.*' => 'exists:zones,id',
        ]);

        $driver->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
        ]);

        if (!empty($validated['password'])) {
            $driver->update(['password' => bcrypt($validated['password'])]);
        }

        if (isset($validated['zones'])) {
            $driver->zones()->sync($validated['zones']);
        }

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver updated successfully.');
    }

    public function destroy(User $driver)
    {
        $driver->zones()->detach();
        $driver->delete();

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver deleted successfully.');
    }
}
