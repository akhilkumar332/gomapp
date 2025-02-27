<?php

namespace App\Http\Controllers;

use App\Services\Monitoring\MonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class StatusController extends Controller
{
    protected $monitoringService;

    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    public function index()
    {
        try {
            // Clear any existing monitoring cache to get fresh results
            Cache::forget('health-status');
            
            $status = $this->monitoringService->check();

            return view('admin.status.index', [
                'status' => $status['results'],
                'timestamp' => $status['timestamp'],
                'overall_status' => $this->getOverallStatus($status['results'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error in status dashboard: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return view('admin.status.index', [
                'error' => 'Error loading system status. Please try again.',
                'status' => [],
                'timestamp' => now(),
                'overall_status' => 'error'
            ]);
        }
    }

    public function refresh(Request $request)
    {
        try {
            // Clear cache to get fresh results
            Cache::forget('health-status');
            
            $status = $this->monitoringService->check();

            if ($request->ajax()) {
                return response()->json([
                    'status' => $status['results'],
                    'timestamp' => $status['timestamp'],
                    'overall_status' => $this->getOverallStatus($status['results'])
                ]);
            }

            return redirect()->route('admin.status.index')
                ->with('success', 'Status refreshed successfully.');
        } catch (\Exception $e) {
            Log::error('Error refreshing status: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Error refreshing system status.',
                    'status' => [],
                    'timestamp' => now(),
                    'overall_status' => 'error'
                ], 500);
            }

            return redirect()->route('admin.status.index')
                ->with('error', 'Error refreshing system status. Please try again.');
        }
    }

    public function getApiDetails()
    {
        try {
            $status = $this->monitoringService->check();
            $apiStatus = $status['results']['api'] ?? null;

            if (!$apiStatus) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'API status not available'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_endpoints' => $apiStatus['total_endpoints'],
                    'status' => $apiStatus['status'],
                    'groups' => $apiStatus['groups'],
                    'metrics' => $apiStatus['metrics'],
                    'last_checked' => $apiStatus['last_checked']
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting API details: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error retrieving API details'
            ], 500);
        }
    }

    public function getDatabaseDetails()
    {
        try {
            $status = $this->monitoringService->check();
            $dbStatus = $status['results']['database'] ?? null;

            if (!$dbStatus) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Database status not available'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'status' => $dbStatus['status'],
                    'connections' => $dbStatus['connections'],
                    'last_checked' => $dbStatus['last_checked']
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting database details: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error retrieving database details'
            ], 500);
        }
    }

    public function getSystemDetails()
    {
        try {
            $status = $this->monitoringService->check();
            $systemStatus = $status['results']['system'] ?? null;

            if (!$systemStatus) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'System status not available'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'status' => $systemStatus['status'],
                    'hostname' => $systemStatus['hostname'],
                    'os' => $systemStatus['os'],
                    'php_version' => $systemStatus['php_version'],
                    'server_software' => $systemStatus['server_software'],
                    'cpu_usage' => $systemStatus['cpu_usage'],
                    'memory' => $systemStatus['memory'],
                    'disk' => $systemStatus['disk'],
                    'last_checked' => $systemStatus['last_checked']
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting system details: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error retrieving system details'
            ], 500);
        }
    }

    public function exportStatus(Request $request)
    {
        try {
            $status = $this->monitoringService->check();
            $format = $request->query('format', 'json');

            switch ($format) {
                case 'json':
                    return response()->json($status)
                        ->header('Content-Disposition', 'attachment; filename=system-status.json');
                
                case 'csv':
                    return $this->exportToCsv($status);
                
                default:
                    return response()->json([
                        'error' => 'Unsupported export format'
                    ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error exporting status: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Error exporting system status'
            ], 500);
        }
    }

    protected function getOverallStatus($results)
    {
        $statuses = collect($results)->pluck('status');
        
        if ($statuses->contains('error')) {
            return 'error';
        }
        
        if ($statuses->contains('warning')) {
            return 'warning';
        }
        
        return 'healthy';
    }

    protected function exportToCsv($status)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=system-status.csv',
        ];

        $callback = function() use ($status) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['Component', 'Status', 'Last Checked', 'Details']);
            
            // Data
            foreach ($status['results'] as $component => $details) {
                fputcsv($file, [
                    $component,
                    $details['status'] ?? 'unknown',
                    ($details['last_checked'] ?? now())->format('Y-m-d H:i:s'),
                    json_encode($details)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
