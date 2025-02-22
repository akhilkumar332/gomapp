<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginLogController extends Controller
{
    /**
     * Display a listing of login logs.
     */
    public function index(Request $request)
    {
        $query = LoginLog::with('user');

        // If user is a driver, only show their own logs
        if (Auth::user()->isDriver()) {
            $query->where('user_id', Auth::id());
        }

        // Apply filters
        if ($request->user_id && Auth::user()->isAdmin()) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->date_range) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('login_at', '>=', $dates[0])
                      ->whereDate('login_at', '<=', $dates[1]);
            }
        }

        $loginLogs = $query->orderBy('login_at', 'desc')->paginate(20);
        $users = User::all();

        return view('admin.login-logs.index', compact('loginLogs', 'users'));
    }

    /**
     * Export login logs
     */
    public function export(Request $request)
    {
        // Only admins can export logs
        if (!Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = LoginLog::with('user');

        // Apply filters
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->date_range) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('login_at', '>=', $dates[0])
                      ->whereDate('login_at', '<=', $dates[1]);
            }
        }

        $logs = $query->orderBy('login_at', 'desc')->get();

        // Transform logs for export
        $exportData = $logs->map(function ($log) {
            return [
                'Date' => $log->login_at->format('Y-m-d H:i:s'),
                'User' => $log->user->name,
                'Role' => $log->user->role,
                'IP Address' => $log->ip_address,
                'User Agent' => $log->user_agent,
                'Location' => $log->location_data ? 
                    ($log->location_data['city'] ?? 'Unknown') . ', ' . 
                    ($log->location_data['country'] ?? 'Unknown') : 
                    'Unknown'
            ];
        });

        // Log the export activity
        activity()
            ->causedBy(Auth::user())
            ->log('Exported login logs');

        return response()->json($exportData);
    }

    /**
     * Show login log details
     */
    public function show(LoginLog $log)
    {
        // Check authorization
        if (Auth::user()->isDriver() && $log->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized to view this log');
        }

        $log->load('user');
        
        return view('admin.login-logs.show', compact('log'));
    }

    /**
     * Get login statistics
     */
    public function statistics(Request $request)
    {
        // Only admins can access statistics
        if (!Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = LoginLog::query();

        // Filter by date range if provided
        if ($request->date_range) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('login_at', '>=', $dates[0])
                      ->whereDate('login_at', '<=', $dates[1]);
            }
        }

        $statistics = [
            'total_logins' => $query->count(),
            'logins_by_user' => $query->selectRaw('users.name, users.role, COUNT(*) as count')
                                    ->join('users', 'login_logs.user_id', '=', 'users.id')
                                    ->groupBy('users.id', 'users.name', 'users.role')
                                    ->get(),
            'logins_by_date' => $query->selectRaw('DATE(login_at) as date, COUNT(*) as count')
                                    ->groupBy('date')
                                    ->orderBy('date', 'desc')
                                    ->get(),
            'logins_by_location' => $query->whereNotNull('location_data')
                                        ->selectRaw('location_data->>"$.country" as country, COUNT(*) as count')
                                        ->groupBy('country')
                                        ->get()
        ];

        return response()->json($statistics);
    }

    /**
     * Record a new login
     */
    public static function recordLogin(Request $request, User $user)
    {
        LoginLog::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at' => now(),
            'location_data' => self::getLocationData($request->ip())
        ]);
    }

    /**
     * Get location data from IP address
     */
    private static function getLocationData($ip)
    {
        try {
            // Use a geolocation service to get location data
            // This is a placeholder - implement actual geolocation logic
            return [
                'city' => 'Unknown',
                'country' => 'Unknown',
                'latitude' => null,
                'longitude' => null
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
