<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\LoginLog;
use App\Models\User;

class LogController extends Controller
{
    /**
     * Show activity logs
     */
    public function activity(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->user_id, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->when($request->action, function ($query, $action) {
                return $query->where('action', 'like', "%{$action}%");
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->whereDate('created_at', '<=', $dateTo);
            })
            ->latest()
            ->paginate(20);

        $users = User::pluck('name', 'id');

        return view('admin.logs.activity', compact('logs', 'users'));
    }

    /**
     * Show login logs
     */
    public function login(Request $request)
    {
        $logs = LoginLog::with('user')
            ->when($request->user_id, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->when($request->ip_address, function ($query, $ip) {
                return $query->where('ip_address', $ip);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->whereDate('login_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->whereDate('login_at', '<=', $dateTo);
            })
            ->latest('login_at')
            ->paginate(20);

        $users = User::pluck('name', 'id');

        return view('admin.logs.login', compact('logs', 'users'));
    }

    /**
     * Show error logs
     */
    public function error()
    {
        // Read Laravel error logs from storage/logs
        $logPath = storage_path('logs/laravel.log');
        $errorLogs = [];

        if (file_exists($logPath)) {
            $logs = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($logs as $log) {
                if (str_contains($log, '[ERROR]') || str_contains($log, 'Stack trace:')) {
                    $errorLogs[] = $log;
                }
            }
        }

        return view('admin.logs.error', compact('errorLogs'));
    }
}
