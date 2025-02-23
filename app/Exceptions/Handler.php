<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Database\QueryException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

class Handler extends ExceptionHandler
{
    use ExceptionHelpers;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'firebase_token',
        'device_token',
        'api_key',
        'secret',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });

        // Convert common exceptions to custom exceptions
        $this->renderable(function (Throwable $e, $request) {
            $e = $this->convertException($e);

            if ($e instanceof DatabaseConnectionException ||
                $e instanceof NetworkException ||
                $e instanceof QueryTimeoutException) {
                return $e->render($request);
            }
        });

        // Handle 404 Not Found
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Resource not found.',
                    'error' => 'not_found',
                    'error_reference' => $this->generateErrorReference($e)
                ], 404);
            }
            return response()->view('errors.404', [
                'exception' => $e,
                'errorReference' => $this->generateErrorReference($e)
            ], 404);
        });

        // Handle Model Not Found
        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Resource not found.',
                    'error' => 'model_not_found',
                    'model' => class_basename($e->getModel()),
                    'error_reference' => $this->generateErrorReference($e)
                ], 404);
            }
            return response()->view('errors.404', [
                'exception' => $e,
                'errorReference' => $this->generateErrorReference($e)
            ], 404);
        });

        // Handle Authentication Exceptions
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'error' => 'unauthenticated',
                    'error_reference' => $this->generateErrorReference($e)
                ], 401);
            }
            return response()->view('errors.401', [
                'exception' => $e,
                'errorReference' => $this->generateErrorReference($e)
            ], 401);
        });

        // Handle Authorization Exceptions
        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'error' => 'unauthorized',
                    'error_reference' => $this->generateErrorReference($e)
                ], 403);
            }
            return response()->view('errors.403', [
                'exception' => $e,
                'errorReference' => $this->generateErrorReference($e)
            ], 403);
        });

        // Handle Validation Exceptions
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                    'error' => 'validation_failed',
                    'error_reference' => $this->generateErrorReference($e)
                ], 422);
            }
            return response()->view('errors.422', [
                'exception' => $e,
                'errors' => $e->errors(),
                'errorReference' => $this->generateErrorReference($e)
            ], 422);
        });

        // Handle Method Not Allowed
        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Method not allowed.',
                    'error' => 'method_not_allowed',
                    'allowed_methods' => $e->getHeaders()['Allow'] ?? null,
                    'error_reference' => $this->generateErrorReference($e)
                ], 405);
            }
            return response()->view('errors.405', [
                'exception' => $e,
                'errorReference' => $this->generateErrorReference($e)
            ], 405);
        });

        // Handle Too Many Requests
        $this->renderable(function (TooManyRequestsHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Too many requests.',
                    'error' => 'too_many_requests',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
                    'error_reference' => $this->generateErrorReference($e)
                ], 429);
            }
            return response()->view('errors.429', [
                'exception' => $e,
                'retryAfter' => $e->getHeaders()['Retry-After'] ?? 60,
                'errorReference' => $this->generateErrorReference($e)
            ], 429);
        });

        // Handle CSRF Token Mismatch
        $this->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'CSRF token mismatch.',
                    'error' => 'csrf_token_mismatch',
                    'error_reference' => $this->generateErrorReference($e)
                ], 419);
            }
            return response()->view('errors.419', [
                'exception' => $e,
                'errorReference' => $this->generateErrorReference($e)
            ], 419);
        });

        // Handle Webhook Exceptions
        $this->renderable(function (WebhookException $e, $request) {
            return $e->render($request);
        });

        // Handle All Other Exceptions
        $this->renderable(function (Throwable $e, $request) {
            // Check if this is a webhook-related error that should be converted
            if ($this->isWebhookRelatedError($e)) {
                $webhookException = $this->convertToWebhookException($e);
                return $webhookException->render($request);
            }

            if (!app()->environment('production')) {
                return null; // Let Laravel handle the exception in non-production
            }

            $errorReference = $this->generateErrorReference($e);
            $this->logError($e, $errorReference);

            if ($request->expectsJson()) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                return response()->json([
                    'message' => 'An unexpected error occurred.',
                    'error' => 'server_error',
                    'error_reference' => $errorReference
                ], $status);
            }

            return response()->view('errors.500', [
                'exception' => $e,
                'errorReference' => $errorReference
            ], 500);
        });
    }

    /**
     * Determine if the exception is webhook-related.
     *
     * @param  \Throwable  $e
     * @return bool
     */
    protected function isWebhookRelatedError(Throwable $e): bool
    {
        // Check if the error occurred in a webhook route
        $request = request();
        if ($request && str_starts_with($request->path(), 'api/webhooks')) {
            return true;
        }

        // Check if the error is from a webhook provider
        $providerPatterns = [
            'firebase' => '/firebase|fcm/i',
            'stripe' => '/stripe/i',
            'twilio' => '/twilio/i'
        ];

        foreach ($providerPatterns as $provider => $pattern) {
            if (preg_match($pattern, $e->getMessage()) || 
                preg_match($pattern, get_class($e))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert a generic exception to a webhook exception.
     *
     * @param  \Throwable  $e
     * @return \App\Exceptions\WebhookException
     */
    protected function convertToWebhookException(Throwable $e): WebhookException
    {
        $provider = $this->detectWebhookProvider($e);
        $event = request()->input('event') ?? request()->input('type');
        
        $context = [
            'original_exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'request_path' => request()->path(),
            'request_method' => request()->method(),
            'request_ip' => request()->ip(),
        ];

        return new WebhookException(
            $e->getMessage(),
            $e->getCode(),
            $e,
            $provider,
            $event,
            $context
        );
    }

    /**
     * Detect the webhook provider from the exception.
     *
     * @param  \Throwable  $e
     * @return string|null
     */
    protected function detectWebhookProvider(Throwable $e): ?string
    {
        $request = request();
        $path = $request->path();
        
        // Check URL path first
        if (str_contains($path, 'webhooks/firebase')) return 'firebase';
        if (str_contains($path, 'webhooks/stripe')) return 'stripe';
        if (str_contains($path, 'webhooks/twilio')) return 'twilio';

        // Check exception class and message
        $exceptionClass = get_class($e);
        $message = $e->getMessage();

        if (str_contains($exceptionClass, 'Firebase') || 
            str_contains($message, 'Firebase')) {
            return 'firebase';
        }

        if (str_contains($exceptionClass, 'Stripe') || 
            str_contains($message, 'Stripe')) {
            return 'stripe';
        }

        if (str_contains($exceptionClass, 'Twilio') || 
            str_contains($message, 'Twilio')) {
            return 'twilio';
        }

        // Check request headers
        $headers = $request->headers->all();
        if (isset($headers['stripe-signature'])) return 'stripe';
        if (isset($headers['x-twilio-signature'])) return 'twilio';
        if (isset($headers['x-firebase-webhook'])) return 'firebase';

        return null;
    }

    /**
     * Generate a unique reference for the error.
     *
     * @param  \Throwable  $e
     * @return string
     */
    protected function generateErrorReference(Throwable $e): string
    {
        return strtoupper(substr(get_class($e), strrpos(get_class($e), '\\') + 1, 3)) . 
               '-' . date('Ymd') . '-' . substr(md5(uniqid()), 0, 6);
    }

    /**
     * Log the error with its reference.
     *
     * @param  \Throwable  $e
     * @param  string  $reference
     * @return void
     */
    protected function logError(Throwable $e, string $reference): void
    {
        logger()->error("Error Reference: {$reference}", [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->id(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'inputs' => request()->except($this->dontFlash),
        ]);
    }
}
