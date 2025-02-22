<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        // If user is a driver, only show their own logs
        if (Auth::user()->isDriver()) {
            $query->where('user_id', Auth::id());
        }

        // Apply filters
        if ($request->user_id && Auth::user()->isAdmin()) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->action) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->date_range) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            }
        }

        $activityLogs = $query->orderBy('created_at', 'desc')->paginate(20);
        $users = User::all();

        return view('admin.activity-logs.index', compact('activityLogs', 'users'));
    }

    /**
     * Get activity statistics
     */
    public function statistics(Request $request)
    {
        // Only admins can access statistics
        if (!Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = ActivityLog::query();

        // Filter by date range if provided
        if ($request->date_range) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            }
        }

        $statistics = [
            'total_activities' => $query->count(),
            'activities_by_type' => $query->selectRaw('action, COUNT(*) as count')
                                        ->groupBy('action')
                                        ->get(),
            'activities_by_user' => $query->selectRaw('users.name, users.role, COUNT(*) as count')
                                        ->join('users', 'activity_logs.user_id', '=', 'users.id')
                                        ->groupBy('users.id', 'users.name', 'users.role')
                                        ->get(),
            'activities_by_date' => $query->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                                        ->groupBy('date')
                                        ->orderBy('date', 'desc')
                                        ->get()
        ];

        return response()->json($statistics);
    }

    /**
     * Export activity logs
     */
    public function export(Request $request)
    {
        // Only admins can export logs
        if (!Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = ActivityLog::with('user');

        // Apply filters
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->action) {
            $query->where('action', $request->action);
        }

        if ($request->date_range) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            }
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        // Transform logs for export
        $exportData = $logs->map(function ($log) {
            return [
                'Date' => $log->created_at->format('Y-m-d H:i:s'),
                'User' => $log->user->name,
                'Role' => $log->user->role,
                'Action' => $log->action,
                'Description' => $log->description,
                'IP Address' => $log->ip_address,
                'User Agent' => $log->user_agent
            ];
        });

        // Log the export activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'export',
            'description' => 'Exported activity logs',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json($exportData);
    }

    /**
     * Show activity log details
     */
    public function show(ActivityLog $log)
    {
        // Check authorization
        if (Auth::user()->isDriver() && $log->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized to view this log');
        }

        $log->load('user');
        
        return view('admin.activity-logs.show', compact('log'));
    }
}
