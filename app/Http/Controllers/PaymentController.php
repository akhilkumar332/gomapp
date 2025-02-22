<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Location;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of the payments.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['location.zone']);

        // If user is a driver, only show payments for their assigned zones
        if (auth()->user()->isDriver()) {
            $zoneIds = auth()->user()->zones->pluck('id');
            $locationIds = Location::whereIn('zone_id', $zoneIds)->pluck('id');
            $query->whereIn('location_id', $locationIds);
        }

        // Apply filters
        if ($request->location_id) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->date_range) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            }
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(10);
        $locations = Location::where('status', 'active')->get();

        return view('admin.payments.index', compact('payments', 'locations'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create()
    {
        $locations = Location::where('status', 'active')->get();
        return view('admin.payments.create', compact('locations'));
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location_id' => 'required|exists:locations,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:online,cash,credit',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,failed'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check if user has access to this location
        $location = Location::findOrFail($request->location_id);
        if (auth()->user()->isDriver() && !auth()->user()->zones->contains($location->zone_id)) {
            return redirect()->back()->with('error', 'Unauthorized to add payment for this location');
        }

        $payment = Payment::create($validator->validated());

        // Log the activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'description' => "Created payment of GHS {$payment->amount} for {$location->shop_name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment created successfully.');
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit(Payment $payment)
    {
        // Check if user has access to this payment's location
        if (auth()->user()->isDriver()) {
            $location = $payment->location;
            if (!auth()->user()->zones->contains($location->zone_id)) {
                return redirect()->back()->with('error', 'Unauthorized to edit this payment');
            }
        }

        $locations = Location::where('status', 'active')->get();
        return view('admin.payments.edit', compact('payment', 'locations'));
    }

    /**
     * Update the specified payment in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        // Only admins can update payments
        if (!auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', 'Unauthorized to update payments');
        }

        $validator = Validator::make($request->all(), [
            'location_id' => 'required|exists:locations,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:online,cash,credit',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,failed'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $payment->update($validator->validated());

        // Log the activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'description' => "Updated payment of GHS {$payment->amount}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment updated successfully.');
    }

    /**
     * Remove the specified payment from storage.
     */
    public function destroy(Request $request, Payment $payment)
    {
        // Only admins can delete payments
        if (!auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', 'Unauthorized to delete payments');
        }

        $amount = $payment->amount;
        $payment->delete();

        // Log the activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'description' => "Deleted payment of GHS {$amount}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    /**
     * Export payments data.
     */
    public function export(Request $request)
    {
        // Only admins can export payments
        if (!auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', 'Unauthorized to export payments');
        }

        $query = Payment::with(['location.zone']);

        // Apply filters
        if ($request->location_id) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_range) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            }
        }

        $payments = $query->get();

        // Log the activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'export',
            'description' => 'Exported payments data',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json($payments);
    }
}
