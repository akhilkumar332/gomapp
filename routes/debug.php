<?php

use Illuminate\Support\Facades\Route;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

Route::get('/debug/chart-data', function () {
    try {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $completed = array_fill(0, 7, 0);
        $total = array_fill(0, 7, 0);
        $collections = array_fill(0, 7, 0);

        // Get data for the current week
        $deliveries = Location::select(
            DB::raw('DAYOFWEEK(completed_at) as day'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'),
            DB::raw('SUM(CASE WHEN payment_received = true THEN payment_amount_received ELSE 0 END) as collections')
        )
        ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])
        ->groupBy('day')
        ->get();

        foreach ($deliveries as $delivery) {
            $index = $delivery->day - 1;
            if (isset($completed[$index])) {
                $completed[$index] = $delivery->completed;
                $total[$index] = $delivery->total;
                $collections[$index] = $delivery->collections;
            }
        }

        return response()->json([
            'labels' => $days,
            'completed' => $completed,
            'total' => $total,
            'collections' => $collections,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
