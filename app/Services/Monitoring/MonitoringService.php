<?php

namespace App\Services\Monitoring;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
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
            Log::error('Error running health checks: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'error',
                'timestamp' => now(),
                'message' => 'Error running health checks: ' . $e->getMessage(),
                'results' => $this->results
            ];
        }
    }

    protected function checkSystem()
    {
        try {
            $cpuLoad = sys_getloadavg();
            $totalSpace = disk_total_space('/');
            $freeSpace = disk_free_space('/');
            $usedSpace = $totalSpace - $freeSpace;
            $memoryUsage = $this->getMemoryUsage();

            $this->results['system'] = [
                'status' => 'healthy',
                'hostname' => gethostname(),
                'os' => PHP_OS,
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'cpu_usage' => [
                    '1m' => $cpuLoad[0],
                    '5m' => $cpuLoad[1],
                    '15m' => $cpuLoad[2]
                ],
                'memory' => $memoryUsage,
                'disk' => [
                    'total' => $totalSpace,
                    'free' => $freeSpace,
                    'used' => $usedSpace,
                    'usage_percentage' => round(($usedSpace / $totalSpace) * 100, 2)
                ],
                'last_checked' => now()
            ];
        } catch (\Exception $e) {
            Log::error('System check failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->results['system'] = [
                'status' => 'error',
                'message' => 'Failed to check system status: ' . $e->getMessage(),
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
            Log::error('API check failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->results['api'] = [
                'status' => 'error',
                'message' => 'Failed to check API endpoints: ' . $e->getMessage(),
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
                    $pdo = $connection->getPdo();
                    $connectionTime = round((microtime(true) - $startTime) * 1000, 2);
                    
                    $metrics = [
                        'connection_time' => $connectionTime,
                        'query_time' => $this->getAverageQueryTime($config['connection']),
                        'active_connections' => $this->getActiveConnections($config['connection'])
                    ];

                    $status = $connectionTime > $this->config['monitors']['database']['thresholds']['connection_time'] 
                        ? 'warning' 
                        : 'healthy';

                    // Get database version based on driver
                    $version = match ($connection->getDriverName()) {
                        'sqlite' => $pdo->query('select sqlite_version()')->fetchColumn(),
                        'mysql' => $pdo->query('select version()')->fetchColumn(),
                        'pgsql' => $pdo->query('show server_version')->fetchColumn(),
                        default => 'unknown'
                    };

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
                        'message' => $e->getMessage()
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
            Log::error('Database check failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->results['database'] = [
                'status' => 'error',
                'message' => 'Failed to check database status: ' . $e->getMessage(),
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
            Log::error('Cache check failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->results['cache'] = [
                'status' => 'error',
                'message' => 'Failed to check cache status: ' . $e->getMessage(),
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
            Log::error('Storage check failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->results['storage'] = [
                'status' => 'error',
                'message' => 'Failed to check storage status: ' . $e->getMessage(),
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
                'processed_jobs' => 0 // You might want to track this in your database
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
            Log::error('Queue check failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->results['queue'] = [
                'status' => 'error',
                'message' => 'Failed to check queue status: ' . $e->getMessage(),
                'last_checked' => now()
            ];
        }
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
        // This would need to be implemented based on your application's logging/monitoring system
        return 0;
    }

    protected function getErrorRate()
    {
        // This would need to be implemented based on your application's logging/monitoring system
        return 0;
    }

    protected function getRequestRate()
    {
        // This would need to be implemented based on your application's logging/monitoring system
        return 0;
    }

    protected function getUptime()
    {
        // This would need to be implemented based on your server's capabilities
        return 0;
    }

    protected function getAverageQueryTime($connection)
    {
        // This would need to be implemented based on your database monitoring system
        return 0;
    }

    protected function getActiveConnections($connection)
    {
        // This would need to be implemented based on your database system
        return 0;
    }

    protected function getMemoryUsage()
    {
        $memory = memory_get_usage(true);
        return [
            'current' => $memory,
            'peak' => memory_get_peak_usage(true),
            'limit' => $this->getMemoryLimit(),
            'usage_percentage' => round(($memory / $this->getMemoryLimit()) * 100, 2)
        ];
    }

    protected function getMemoryLimit()
    {
        $memoryLimit = ini_get('memory_limit');
        if ($memoryLimit === '-1') {
            return PHP_INT_MAX;
        }
        return $this->convertToBytes($memoryLimit);
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
}
