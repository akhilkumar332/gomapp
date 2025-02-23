<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class DatabaseConnectionException extends Exception
{
    protected $view = 'errors.database';
    protected $errorReference;

    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        $this->errorReference = $this->generateErrorReference();
        
        parent::__construct(
            $message ?? 'Database connection error occurred',
            $code,
            $previous
        );

        $this->logError();
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'error' => 'database_connection_error',
                'error_reference' => $this->errorReference
            ], 503);
        }

        return response()->view($this->view, [
            'exception' => $this,
            'errorReference' => $this->errorReference
        ], 503);
    }

    protected function generateErrorReference()
    {
        return 'DB-' . date('Ymd') . '-' . substr(md5(uniqid()), 0, 6);
    }

    protected function logError()
    {
        Log::error('Database Connection Error', [
            'error_reference' => $this->errorReference,
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString(),
            'connection' => config('database.default'),
            'database' => config('database.connections.' . config('database.default') . '.database'),
            'host' => config('database.connections.' . config('database.default') . '.host'),
        ]);
    }
}

class NetworkException extends Exception
{
    protected $view = 'errors.network';
    protected $errorReference;

    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        $this->errorReference = $this->generateErrorReference();
        
        parent::__construct(
            $message ?? 'Network error occurred',
            $code,
            $previous
        );

        $this->logError();
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'error' => 'network_error',
                'error_reference' => $this->errorReference
            ], 503);
        }

        return response()->view($this->view, [
            'exception' => $this,
            'errorReference' => $this->errorReference
        ], 503);
    }

    protected function generateErrorReference()
    {
        return 'NET-' . date('Ymd') . '-' . substr(md5(uniqid()), 0, 6);
    }

    protected function logError()
    {
        Log::error('Network Error', [
            'error_reference' => $this->errorReference,
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString(),
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method(),
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip(),
        ]);
    }
}

class QueryTimeoutException extends QueryException
{
    protected $view = 'errors.database';
    protected $errorReference;

    public function __construct($connection, $sql, array $bindings, Exception $previous)
    {
        parent::__construct($connection, $sql, $bindings, $previous);
        $this->errorReference = $this->generateErrorReference();
        $this->logError();
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Database query timed out',
                'error' => 'query_timeout',
                'error_reference' => $this->errorReference
            ], 504);
        }

        return response()->view($this->view, [
            'exception' => $this,
            'errorReference' => $this->errorReference,
            'timeout' => true
        ], 504);
    }

    protected function generateErrorReference()
    {
        return 'QT-' . date('Ymd') . '-' . substr(md5(uniqid()), 0, 6);
    }

    protected function logError()
    {
        Log::error('Query Timeout', [
            'error_reference' => $this->errorReference,
            'connection' => $this->connection,
            'sql' => $this->sql,
            'bindings' => $this->bindings,
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString()
        ]);
    }
}

trait ExceptionHelpers
{
    /**
     * Determine if the exception is a database connection error
     *
     * @param  \Exception  $e
     * @return bool
     */
    protected function isDatabaseConnectionError(Exception $e)
    {
        return $e instanceof QueryException && 
               str_contains($e->getMessage(), 'SQLSTATE[HY000] [2002]');
    }

    /**
     * Determine if the exception is a network error
     *
     * @param  \Exception  $e
     * @return bool
     */
    protected function isNetworkError(Exception $e)
    {
        return $e instanceof \GuzzleHttp\Exception\ConnectException ||
               $e instanceof \GuzzleHttp\Exception\RequestException ||
               ($e instanceof \RuntimeException && 
                str_contains($e->getMessage(), 'Failed to connect'));
    }

    /**
     * Determine if the exception is a query timeout
     *
     * @param  \Exception  $e
     * @return bool
     */
    protected function isQueryTimeout(Exception $e)
    {
        return $e instanceof QueryException && 
               str_contains($e->getMessage(), 'SQLSTATE[HY000]: General error: 2006');
    }

    /**
     * Convert common exceptions to custom exceptions
     *
     * @param  \Exception  $e
     * @return \Exception
     */
    protected function convertException(Exception $e)
    {
        if ($this->isDatabaseConnectionError($e)) {
            return new DatabaseConnectionException(
                'Unable to connect to the database',
                0,
                $e
            );
        }

        if ($this->isNetworkError($e)) {
            return new NetworkException(
                'Network connection error',
                0,
                $e
            );
        }

        if ($this->isQueryTimeout($e)) {
            return new QueryTimeoutException(
                $e->connection,
                $e->sql,
                $e->bindings,
                $e->getPrevious()
            );
        }

        return $e;
    }
}
