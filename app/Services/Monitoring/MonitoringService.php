<?php

namespace App\Services\Monitoring;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MonitoringService
{
    protected $config;
    protected $results = [];

    public function __construct()
    {
        $this->config = config('monitoring');
    }

    public function check()
    {
        if ($this->config['cache']['enabled']) {
            return Cache::remember($this->config['cache']['key'], $this->config['cache']['ttl'], function () {
                return $this->runChecks();
            });
        }

        return $this->runChecks();
    }

    protected function runChecks()
    {
        try {
            $startTime = microtime(true);

            // Run all enabled checks
            $this->checkSystem();
            $this->checkApi();
            $this->checkDatabase();
            $this->checkCache();
            $this->checkStorage();
            $this->checkQueue();

            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2); // in milliseconds

            return [
                'status' => $this->getOverallStatus(),
                'timestamp' => now(),
                'execution_time' => $executionTime,
                'results' => $this->results
            ];
        } catch (\Exception $e) {
            Log::error('Error running health checks: ' . $e->getMessage());
            return [
                'status' => 'error',
                'timestamp' => now(),
                'message' => 'Error running health checks',
                'results' => $this->results
            ];
        }
    }

    protected function checkSystem()
    {
        try {
            // Safe system information gathering for shared hosting
            $systemInfo = [
                'status' => 'healthy',
                'hostname' => gethostname() ?: 'Unknown',
                'os' => PHP_OS,
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'memory' => $this->getMemoryUsageSafe(),
                'disk' => $this->getDiskUsageSafe(),
                'cpu_usage' => $this->getCpuUsageSafe(),
                'last_checked' => now()
            ];

            $this->results['system'] = $systemInfo;
        } catch (\Exception $e) {
            Log::error('System check failed: ' . $e->getMessage());
            $this->results['system'] = [
                'status' => 'error',
                'message' => 'System information unavailable',
                'last_checked' => now()
            ];
        }
    }

    protected function checkApi()
    {
        try {
            $routes = Route::getRoutes();
            $apiRoutes = collect($routes)->filter(function ($route) {
                return Str::startsWith($route->uri(), 'api/') &&
                       !$this->isExcludedRoute($route->uri());
            })->groupBy(function ($route) {
                return $this->getRouteGroup($route);
            })->map(function ($routes, $group) {
                return [
                    'total' => $routes->count(),
                    'methods' => $routes->pluck('methods')->flatten()->unique()->values(),
                    'endpoints' => $routes->map(function ($route) {
                        return [
                            'uri' => $route->uri(),
                            'methods' => $route->methods(),
                            'name' => $route->getName(),
                            'action' => $this->getRouteAction($route),
                            'middleware' => $route->middleware()
                        ];
                    })->values()
                ];
            });

            $metrics = [
                'response_time' => $this->getAverageResponseTime(),
                'error_rate' => $this->getErrorRate(),
                'request_rate' => $this->getRequestRate()
            ];

            $this->results['api'] = [
                'status' => 'healthy',
                'total_endpoints' => $apiRoutes->sum('total'),
                'groups' => $apiRoutes->toArray(),
                'metrics' => $metrics,
                'last_checked' => now()
            ];
        } catch (\Exception $e) {
            Log::error('API check failed: ' . $e->getMessage());
            $this->results['api'] = [
                'status' => 'error',
                'message' => 'API check failed',
                'last_checked' => now()
            ];
        }
    }

    protected function checkDatabase()
    {
        try {
            $dbStatus = [];
            
            foreach ($this->config['monitors']['database']['connections'] as $name => $config) {
                try {
                    $startTime = microtime(true);
                    $connection = DB::connection($config['connection']);
                    
                    // Simple query to test connection
                    $connection->getPdo()->query('SELECT 1');
                    
                    $connectionTime = round((microtime(true) - $startTime) * 1000, 2);
                    
                    $metrics = [
                        'connection_time' => $connectionTime,
                        'query_time' => 0, // Simplified for shared hosting
                        'active_connections' => 1 // Simplified for shared hosting
                    ];

                    $status = $connectionTime > $this->config['monitors']['database']['thresholds']['connection_time'] 
                        ? 'warning' 
                        : 'healthy';

                    // Safe version check
                    $version = 'Unknown';
                    try {
                        $pdo = $connection->getPdo();
                        $version = match ($connection->getDriverName()) {
                            'sqlite' => $pdo->query('select sqlite_version()')->fetchColumn(),
                            'mysql' => $pdo->query('select version()')->fetchColumn(),
                            'pgsql' => $pdo->query('show server_version')->fetchColumn(),
                            default => 'Unknown'
                        };
                    } catch (\Exception $e) {
                        // Version check failed, continue with unknown version
                    }

                    $dbStatus[$name] = [
                        'status' => $status,
                        'connection_time' => $connectionTime,
                        'metrics' => $metrics,
                        'version' => $version,
                        'driver' => $connection->getDriverName()
                    ];
                } catch (\Exception $e) {
                    $dbStatus[$name] = [
                        'status' => 'error',
                        'message' => 'Connection failed'
                    ];
                }
            }

            $this->results['database'] = [
                'status' => collect($dbStatus)->every(fn($status) => $status['status'] === 'healthy') 
                    ? 'healthy' 
                    : (collect($dbStatus)->contains('status', 'error') ? 'error' : 'warning'),
                'connections' => $dbStatus,
                'last_checked' => now()
            ];
        } catch (\Exception $e) {
            Log::error('Database check failed: ' . $e->getMessage());
            $this->results['database'] = [
                'status' => 'error',
                'message' => 'Database check failed',
                'last_checked' => now()
            ];
        }
    }

    protected function checkCache()
    {
        try {
            $metrics = [];
            foreach ($this->config['monitors']['cache']['stores'] as $store) {
                $key = 'health-check-' . Str::random(8);
                $value = Str::random(16);
                
                $startTime = microtime(true);
                Cache::store($store)->put($key, $value, 1);
                $writeTime = round((microtime(true) - $startTime) * 1000, 2);
                
                $startTime = microtime(true);
                $stored = Cache::store($store)->get($key) === $value;
                $readTime = round((microtime(true) - $startTime) * 1000, 2);
                
                Cache::store($store)->forget($key);

                $metrics[$store] = [
                    'status' => $stored ? 'healthy' : 'error',
                    'write_time' => $writeTime,
                    'read_time' => $readTime
                ];
            }

            $this->results['cache'] = [
                'status' => collect($metrics)->every(fn($m) => $m['status'] === 'healthy') ? 'healthy' : 'error',
                'driver' => config('cache.default'),
                'stores' => $metrics,
                'last_checked' => now()
            ];
        } catch (\Exception $e) {
            Log::error('Cache check failed: ' . $e->getMessage());
            $this->results['cache'] = [
                'status' => 'error',
                'message' => 'Cache check failed',
                'last_checked' => now()
            ];
        }
    }

    protected function checkStorage()
    {
        try {
            $metrics = [];
            foreach ($this->config['monitors']['storage']['disks'] as $disk) {
                try {
                    $storage = Storage::disk($disk);
                    $testFile = 'health-check-' . Str::random(8) . '.txt';
                    
                    // Test write
                    $startTime = microtime(true);
                    $storage->put($testFile, 'test');
                    $writeTime = round((microtime(true) - $startTime) * 1000, 2);
                    
                    // Test read
                    $startTime = microtime(true);
                    $content = $storage->get($testFile);
                    $readTime = round((microtime(true) - $startTime) * 1000, 2);
                    
                    // Cleanup
                    $storage->delete($testFile);

                    $diskMetrics = [
                        'write_time' => $writeTime,
                        'read_time' => $readTime,
                        'writable' => true,
                        'readable' => $content === 'test'
                    ];

                    // Get disk space info if possible
                    $path = $storage->path('');
                    if (is_dir($path)) {
                        $diskMetrics['total_space'] = disk_total_space($path);
                        $diskMetrics['free_space'] = disk_free_space($path);
                        $diskMetrics['used_space'] = $diskMetrics['total_space'] - $diskMetrics['free_space'];
                        $diskMetrics['usage_percentage'] = round(($diskMetrics['used_space'] / $diskMetrics['total_space']) * 100, 2);
                    }

                    $metrics[$disk] = array_merge([
                        'status' => 'healthy'
                    ], $diskMetrics);
                } catch (\Exception $e) {
                    $metrics[$disk] = [
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }
            }

            $this->results['storage'] = [
                'status' => collect($metrics)->every(fn($m) => $m['status'] === 'healthy') ? 'healthy' : 'error',
                'disks' => $metrics,
                'last_checked' => now()
            ];
        } catch (\Exception $e) {
            Log::error('Storage check failed: ' . $e->getMessage());
            $this->results['storage'] = [
                'status' => 'error',
                'message' => 'Storage check failed',
                'last_checked' => now()
            ];
        }
    }

    protected function checkQueue()
    {
        try {
            $metrics = [
                'failed_jobs' => DB::table('failed_jobs')->count(),
                'pending_jobs' => Queue::size(),
                'processed_jobs' => 0 // Simplified for shared hosting
            ];

            $status = 'healthy';
            if ($metrics['failed_jobs'] > $this->config['monitors']['queue']['thresholds']['max_failed_jobs']) {
                $status = 'warning';
            }
            if ($metrics['pending_jobs'] > $this->config['monitors']['queue']['thresholds']['max_pending_jobs']) {
                $status = 'warning';
            }

            $this->results['queue'] = [
                'status' => $status,
                'metrics' => $metrics,
                'last_checked' => now()
            ];
        } catch (\Exception $e) {
            Log::error('Queue check failed: ' . $e->getMessage());
            $this->results['queue'] = [
                'status' => 'error',
                'message' => 'Queue check failed',
                'last_checked' => now()
            ];
        }
    }

    protected function getMemoryUsageSafe()
    {
        try {
            $memoryLimit = $this->getMemoryLimit();
            $currentUsage = memory_get_usage(true);
            $peakUsage = memory_get_peak_usage(true);
            
            return [
                'current' => $currentUsage,
                'peak' => $peakUsage,
                'limit' => $memoryLimit,
                'usage_percentage' => $memoryLimit > 0 ? round(($currentUsage / $memoryLimit) * 100, 2) : 0
            ];
        } catch (\Exception $e) {
            return [
                'current' => 0,
                'peak' => 0,
                'limit' => 0,
                'usage_percentage' => 0
            ];
        }
    }

    protected function getDiskUsageSafe()
    {
        try {
            $path = Storage::disk('local')->path('');
            if (function_exists('disk_free_space') && function_exists('disk_total_space')) {
                $total = disk_total_space($path);
                $free = disk_free_space($path);
                $used = $total - $free;
                $usagePercentage = ($used / $total) * 100;

                return [
                    'total' => $total,
                    'free' => $free,
                    'used' => $used,
                    'usage_percentage' => round($usagePercentage, 2)
                ];
            }
        } catch (\Exception $e) {
            // Fallback for restricted environments
        }

        return [
            'total' => 0,
            'free' => 0,
            'used' => 0,
            'usage_percentage' => 0
        ];
    }

    protected function getCpuUsageSafe()
    {
        try {
            if (function_exists('sys_getloadavg')) {
                $load = sys_getloadavg();
                return [
                    '1m' => $load[0],
                    '5m' => $load[1],
                    '15m' => $load[2]
                ];
            }
        } catch (\Exception $e) {
            // Fallback for restricted environments
        }

        return [
            '1m' => 0,
            '5m' => 0,
            '15m' => 0
        ];
    }

    protected function getMemoryLimit()
    {
        try {
            $memoryLimit = ini_get('memory_limit');
            if ($memoryLimit === '-1') {
                return PHP_INT_MAX;
            }
            return $this->convertToBytes($memoryLimit);
        } catch (\Exception $e) {
            return PHP_INT_MAX;
        }
    }

    protected function convertToBytes($value)
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value)-1]);
        $value = (int)$value;
        
        switch($last) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        
        return $value;
    }

    protected function getOverallStatus()
    {
        $statuses = collect($this->results)->pluck('status');
        
        if ($statuses->contains('error')) {
            return 'error';
        }
        
        if ($statuses->contains('warning')) {
            return 'warning';
        }
        
        return 'healthy';
    }

    protected function isExcludedRoute($uri)
    {
        return collect($this->config['monitors']['api']['exclude_patterns'])
            ->some(fn($pattern) => Str::is($pattern, $uri));
    }

    protected function getRouteGroup($route)
    {
        $uri = $route->uri();
        foreach ($this->config['monitors']['api']['groups'] as $key => $group) {
            if (Str::is($group['pattern'], $uri)) {
                return $key;
            }
        }
        return 'other';
    }

    protected function getRouteAction($route)
    {
        $action = $route->getActionName();
        if ($action === 'Closure') {
            return 'Closure';
        }
        return Str::after($action, '\\');
    }

    protected function getAverageResponseTime()
    {
        // Simplified for shared hosting
        return 0;
    }

    protected function getErrorRate()
    {
        // Simplified for shared hosting
        return 0;
    }

    protected function getRequestRate()
    {
        // Simplified for shared hosting
        return 0;
    }
}
