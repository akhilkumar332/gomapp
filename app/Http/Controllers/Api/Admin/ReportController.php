<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Location;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    /**
     * Get activity report
     */
    public function activity(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'nullable|exists:users,id',
            'action' => 'nullable|string',
        ]);

        $query = ActivityLog::with('user')
            ->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->action) {
            $query->where('action', $request->action);
        }

        $activities = $query->latest()->get();

        $data = [
            'total_activities' => $activities->count(),
            'activities_by_type' => $activities->groupBy('action')
                ->map(fn($items) => $items->count()),
            'activities_by_user' => $activities->groupBy('user_id')
                ->map(function($items) {
                    $user = $items->first()->user;
                    return [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'role' => $user->role,
                        ],
                        'count' => $items->count(),
                    ];
                })->values(),
            'activities' => $activities->map(function($activity) {
                return [
                    'id' => $activity->id,
                    'action' => $activity->action,
                    'description' => $activity->description,
                    'user' => $activity->user ? [
                        'id' => $activity->user->id,
                        'name' => $activity->user->name,
                        'role' => $activity->user->role,
                    ] : null,
                    'created_at' => $activity->created_at,
                ];
            }),
        ];

        return response()->json(['data' => $data]);
    }

    /**
     * Get performance report
     */
    public function performance(Request $request)
    {
        $request->validate([
            'zone_id' => 'nullable|exists:zones,id',
            'driver_id' => 'nullable|exists:users,id',
            'period' => 'required|in:daily,weekly,monthly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $query = Location::query()
            ->when($request->zone_id, fn($q) => $q->where('zone_id', $request->zone_id))
            ->when($request->driver_id, fn($q) => $q->where('completed_by', $request->driver_id))
            ->whereBetween('completed_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);

        // Calculate metrics
        $totalDeliveries = $query->count();
        $successfulDeliveries = $query->where('status', 'completed')->count();
        $totalCollections = $query->where('payment_received', true)->sum('payment_amount_received');
        $averageDeliveryTime = $query->whereNotNull('started_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_time'))
            ->first()
            ->avg_time ?? 0;

        // Get performance by period
        $performanceByPeriod = $query->select(
            DB::raw('DATE(completed_at) as date'),
            DB::raw('COUNT(*) as total_deliveries'),
            DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_deliveries'),
            DB::raw('SUM(CASE WHEN payment_received = 1 THEN payment_amount_received ELSE 0 END) as collections')
        )
        ->groupBy('date')
        ->get();

        $data = [
            'summary' => [
                'total_deliveries' => $totalDeliveries,
                'successful_deliveries' => $successfulDeliveries,
                'success_rate' => $totalDeliveries > 0 ? ($successfulDeliveries / $totalDeliveries) * 100 : 0,
                'total_collections' => $totalCollections,
                'average_delivery_time' => round($averageDeliveryTime),
            ],
            'performance_by_period' => $performanceByPeriod->map(function($item) {
                return [
                    'date' => $item->date,
                    'total_deliveries' => $item->total_deliveries,
                    'successful_deliveries' => $item->successful_deliveries,
                    'success_rate' => ($item->total_deliveries > 0) 
                        ? ($item->successful_deliveries / $item->total_deliveries) * 100 
                        : 0,
                    'collections' => $item->collections,
                ];
            }),
        ];

        if ($request->zone_id) {
            $zone = Zone::find($request->zone_id);
            $data['zone'] = [
                'id' => $zone->id,
                'name' => $zone->name,
            ];
        }

        if ($request->driver_id) {
            $driver = User::find($request->driver_id);
            $data['driver'] = [
                'id' => $driver->id,
                'name' => $driver->name,
            ];
        }

        return response()->json(['data' => $data]);
    }

    /**
     * Export report data
     */
    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:activity,performance',
            'format' => 'required|in:csv,xlsx',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'zone_id' => 'nullable|exists:zones,id',
            'driver_id' => 'nullable|exists:users,id',
        ]);

        // Get data based on report type
        $data = $request->type === 'activity' 
            ? $this->getActivityData($request)
            : $this->getPerformanceData($request);

        // Generate CSV content
        $csv = $this->generateCsv($data, $request->type);

        // Create response
        $filename = sprintf(
            '%s_report_%s_to_%s.csv',
            $request->type,
            $request->start_date,
            $request->end_date
        );

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get activity data for export
     */
    private function getActivityData(Request $request)
    {
        return ActivityLog::with('user')
            ->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ])
            ->latest()
            ->get()
            ->map(function($activity) {
                return [
                    'Date' => $activity->created_at->format('Y-m-d H:i:s'),
                    'Action' => $activity->action,
                    'Description' => $activity->description,
                    'User' => $activity->user ? $activity->user->name : 'System',
                    'Role' => $activity->user ? $activity->user->role : 'N/A',
                ];
            });
    }

    /**
     * Get performance data for export
     */
    private function getPerformanceData(Request $request)
    {
        return Location::query()
            ->when($request->zone_id, fn($q) => $q->where('zone_id', $request->zone_id))
            ->when($request->driver_id, fn($q) => $q->where('completed_by', $request->driver_id))
            ->whereBetween('completed_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ])
            ->with(['zone', 'completedBy'])
            ->get()
            ->map(function($location) {
                return [
                    'Date' => $location->completed_at->format('Y-m-d H:i:s'),
                    'Zone' => $location->zone->name,
                    'Driver' => $location->completedBy ? $location->completedBy->name : 'N/A',
                    'Status' => $location->status,
                    'Delivery Time (mins)' => $location->started_at && $location->completed_at
                        ? $location->started_at->diffInMinutes($location->completed_at)
                        : 'N/A',
                    'Payment Received' => $location->payment_received ? 'Yes' : 'No',
                    'Amount' => $location->payment_amount_received ?? 0,
                ];
            });
    }

    /**
     * Generate CSV content
     */
    private function generateCsv($data, $type)
    {
        if ($data->isEmpty()) {
            return "No data available for the selected period.\n";
        }

        // Get headers from first row
        $headers = array_keys($data->first()->toArray());

        // Start with headers
        $csv = implode(',', $headers) . "\n";

        // Add rows
        foreach ($data as $row) {
            $csv .= implode(',', array_map(function($field) {
                // Escape fields that contain comma or quotes
                return is_string($field) && (strpos($field, ',') !== false || strpos($field, '"') !== false)
                    ? '"' . str_replace('"', '""', $field) . '"'
                    : $field;
            }, $row->toArray())) . "\n";
        }

        return $csv;
    }
}
