<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\DatabaseConnectionException;
use App\Exceptions\NetworkException;

class HealthCheckController extends Controller
{
    /**
     * Check overall system health
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $checks = [
            'mysql' => $this->checkDatabase('mysql'),
            'sqlite' => $this->checkDatabase('sqlite'),
                'cache' => $this->checkCache(),
                'storage' => $this->checkStorage(),
                'queue' => $this->checkQueue(),
                'redis' => $this->checkRedis(),
                'firebase' => $this->checkFirebase(),
            ];

            $status = !in_array(false, array_values($checks));

            return response()->json([
                'status' => $status ? 'healthy' : 'unhealthy',
                'timestamp' => now()->toIso8601String(),
                'checks' => $checks,
                'environment' => app()->environment(),
                'version' => config('app.version', '1.0.0')
            ], $status ? 200 : 503);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Health check failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check database connection
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function database()
    {
        try {
            // Test database connection
            DB::connection()->getPdo();

            // Test query execution
            $result = DB::select('SELECT 1');

            return response()->json([
                'status' => 'connected',
                'connection' => config('database.default'),
                'database' => DB::connection()->getDatabaseName(),
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            throw new DatabaseConnectionException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Check network connectivity
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function network()
    {
        try {
            // Test external connectivity
            $client = new \GuzzleHttp\Client(['timeout' => 5]);
            $response = $client->get('https://api.github.com/status');

            if ($response->getStatusCode() !== 200) {
                throw new NetworkException('External API check failed');
            }

            return response()->json([
                'status' => 'connected',
                'external_connectivity' => true,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            throw new NetworkException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Check database connection
     *
     * @return bool
     */
    protected function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Check cache availability
     *
     * @return bool
     */
    protected function checkCache()
    {
        try {
            $key = 'health-check-' . str_random(10);
            Cache::put($key, true, 1);
            $value = Cache::get($key);
            Cache::forget($key);
            return $value === true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Check storage accessibility
     *
     * @return bool
     */
    protected function checkStorage()
    {
        try {
            $filename = 'health-check-' . str_random(10);
            Storage::put($filename, 'Health Check');
            $exists = Storage::exists($filename);
            Storage::delete($filename);
            return $exists;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Check queue connection
     *
     * @return bool
     */
    protected function checkQueue()
    {
        try {
            $connection = config('queue.default');
            $queue = config("queue.connections.{$connection}.queue");
            
            // For database queue
            if ($connection === 'database') {
                return DB::table('jobs')->count() >= 0;
            }
            
            // For Redis queue
            if ($connection === 'redis') {
                return Redis::ping() == 'PONG';
            }

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Check Redis connection
     *
     * @return bool
     */
    protected function checkRedis()
    {
        try {
            return Redis::ping() == 'PONG';
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Check Firebase connection
     *
     * @return bool
     */
    protected function checkFirebase()
    {
        try {
            if (!config('firebase.enabled')) {
                return null;
            }

            $firebase = app(\App\Services\FirebaseService::class);
            return $firebase !== null;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Get system metrics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function metrics()
    {
        try {
            return response()->json([
                'memory' => [
                    'usage' => memory_get_usage(true),
                    'peak' => memory_get_peak_usage(true),
                ],
                'database' => [
                    'connections' => DB::getConnections(),
                    'queries' => DB::getQueryLog(),
                ],
                'cache' => [
                    'driver' => config('cache.default'),
                    'store' => config('cache.stores.' . config('cache.default')),
                ],
                'queue' => [
                    'driver' => config('queue.default'),
                    'connection' => config('queue.connections.' . config('queue.default')),
                ],
                'session' => [
                    'driver' => config('session.driver'),
                    'lifetime' => config('session.lifetime'),
                ],
                'storage' => [
                    'driver' => config('filesystems.default'),
                    'root' => config('filesystems.disks.' . config('filesystems.default') . '.root'),
                ],
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve metrics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
