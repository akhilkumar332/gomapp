<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = User::where('role', 'driver')
            ->with(['zones'])
            ->latest()
            ->paginate(10);

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
            'phone_number' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8',
            'zones' => 'nullable|array',
            'zones.*' => 'exists:zones,id',
        ]);

        $driver = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'password' => bcrypt($validated['password']),
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
