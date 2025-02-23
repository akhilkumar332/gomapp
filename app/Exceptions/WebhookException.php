<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookException extends Exception
{
    /**
     * The webhook provider.
     *
     * @var string|null
     */
    protected $provider;

    /**
     * The webhook event.
     *
     * @var string|null
     */
    protected $event;

    /**
     * Additional context data.
     *
     * @var array
     */
    protected $context;

    /**
     * Create a new webhook exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  \Exception|null  $previous
     * @param  string|null  $provider
     * @param  string|null  $event
     * @param  array  $context
     * @return void
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        Exception $previous = null,
        string $provider = null,
        string $event = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);

        $this->provider = $provider;
        $this->event = $event;
        $this->context = $context;

        $this->logException();
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render(Request $request)
    {
        $data = [
            'error' => 'webhook_error',
            'message' => $this->getMessage(),
            'provider' => $this->provider,
            'event' => $this->event,
        ];

        // Add debug information in non-production environments
        if (!app()->environment('production')) {
            $data['debug'] = [
                'code' => $this->getCode(),
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'context' => $this->context,
            ];
        }

        $statusCode = $this->getStatusCode();

        if ($request->expectsJson()) {
            return response()->json($data, $statusCode);
        }

        return response()->view('errors.webhook', [
            'exception' => $this,
            'provider' => $this->provider,
            'event' => $this->event,
            'context' => $this->context,
            'statusCode' => $statusCode,
        ], $statusCode);
    }

    /**
     * Get the appropriate HTTP status code.
     *
     * @return int
     */
    protected function getStatusCode(): int
    {
        // Use the exception code if it's a valid HTTP status code
        if ($this->code >= 100 && $this->code < 600) {
            return $this->code;
        }

        // Map common error messages to status codes
        $messagePatterns = [
            '/invalid.*signature/i' => 401,
            '/missing.*signature/i' => 400,
            '/invalid.*token/i' => 401,
            '/expired.*token/i' => 401,
            '/not.*found/i' => 404,
            '/permission.*denied/i' => 403,
            '/rate.*limit/i' => 429,
            '/invalid.*payload/i' => 400,
            '/validation.*failed/i' => 422,
            '/duplicate/i' => 409,
        ];

        foreach ($messagePatterns as $pattern => $code) {
            if (preg_match($pattern, $this->getMessage())) {
                return $code;
            }
        }

        // Default to 400 Bad Request
        return 400;
    }

    /**
     * Log the webhook exception.
     *
     * @return void
     */
    protected function logException(): void
    {
        $logData = [
            'provider' => $this->provider,
            'event' => $this->event,
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'context' => $this->context,
        ];

        if ($previous = $this->getPrevious()) {
            $logData['previous'] = [
                'message' => $previous->getMessage(),
                'code' => $previous->getCode(),
                'file' => $previous->getFile(),
                'line' => $previous->getLine(),
            ];
        }

        // Determine log level based on status code
        $statusCode = $this->getStatusCode();
        if ($statusCode >= 500) {
            Log::error('Webhook error: ' . $this->getMessage(), $logData);
        } elseif ($statusCode >= 400) {
            Log::warning('Webhook warning: ' . $this->getMessage(), $logData);
        } else {
            Log::info('Webhook info: ' . $this->getMessage(), $logData);
        }

        // Send alerts for critical webhook errors
        if ($this->shouldSendAlert()) {
            $this->sendAlert();
        }
    }

    /**
     * Determine if an alert should be sent for this exception.
     *
     * @return bool
     */
    protected function shouldSendAlert(): bool
    {
        // Check if alerts are enabled for this provider
        if (!config("webhooks.{$this->provider}.monitoring.alert_on_failure", false)) {
            return false;
        }

        // Check if this error type should trigger an alert
        $alertableErrors = config("webhooks.{$this->provider}.monitoring.alertable_errors", []);
        if (!empty($alertableErrors)) {
            $messageMatches = false;
            foreach ($alertableErrors as $pattern) {
                if (preg_match($pattern, $this->getMessage())) {
                    $messageMatches = true;
                    break;
                }
            }
            if (!$messageMatches) {
                return false;
            }
        }

        // Check error threshold
        $threshold = config("webhooks.{$this->provider}.monitoring.alert_threshold", 1);
        $interval = config("webhooks.{$this->provider}.monitoring.alert_interval", 60);
        
        $cacheKey = "webhook_errors:{$this->provider}";
        $errorCount = cache()->increment($cacheKey);
        
        if ($errorCount === 1) {
            cache()->put($cacheKey, 1, now()->addMinutes($interval));
        }

        return $errorCount >= $threshold;
    }

    /**
     * Send an alert for this exception.
     *
     * @return void
     */
    protected function sendAlert(): void
    {
        $channels = config("webhooks.{$this->provider}.monitoring.notification_channels", ['mail']);

        foreach ($channels as $channel) {
            try {
                // Implementation of different notification channels
                switch ($channel) {
                    case 'mail':
                        // Send email notification
                        break;
                    case 'slack':
                        // Send Slack notification
                        break;
                    case 'telegram':
                        // Send Telegram notification
                        break;
                }
            } catch (\Exception $e) {
                Log::error("Failed to send webhook alert via {$channel}", [
                    'error' => $e->getMessage(),
                    'webhook_error' => $this->getMessage(),
                    'provider' => $this->provider,
                ]);
            }
        }
    }

    /**
     * Get the webhook provider.
     *
     * @return string|null
     */
    public function getProvider(): ?string
    {
        return $this->provider;
    }

    /**
     * Get the webhook event.
     *
     * @return string|null
     */
    public function getEvent(): ?string
    {
        return $this->event;
    }

    /**
     * Get the additional context.
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
