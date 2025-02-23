<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\WebhookEventDispatcher;
use Illuminate\Support\Facades\Log;

class ProcessWebhookRetry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $provider;
    protected $event;
    protected $payload;
    protected $attempt;
    protected $originalException;

    /**
     * Create a new job instance.
     *
     * @param  string  $provider
     * @param  string  $event
     * @param  array  $payload
     * @param  int  $attempt
     * @param  \Exception|null  $originalException
     * @return void
     */
    public function __construct(string $provider, string $event, array $payload, int $attempt, \Exception $originalException = null)
    {
        $this->provider = $provider;
        $this->event = $event;
        $this->payload = $payload;
        $this->attempt = $attempt;
        $this->originalException = $originalException;

        // Set queue configuration from webhook config
        $this->onQueue(config('webhooks.global.queue.queue', 'webhooks'));
        $this->onConnection(config('webhooks.global.queue.connection', 'redis'));
    }

    /**
     * Execute the job.
     *
     * @param  \App\Services\WebhookEventDispatcher  $dispatcher
     * @return void
     */
    public function handle(WebhookEventDispatcher $dispatcher)
    {
        try {
            Log::info("Retrying webhook: {$this->provider}.{$this->event}", [
                'attempt' => $this->attempt,
                'payload' => $this->payload,
            ]);

            $dispatcher->dispatch($this->provider, $this->event, $this->payload);

            Log::info("Webhook retry successful: {$this->provider}.{$this->event}", [
                'attempt' => $this->attempt,
            ]);
        } catch (\Exception $e) {
            Log::error("Webhook retry failed: {$this->provider}.{$this->event}", [
                'attempt' => $this->attempt,
                'error' => $e->getMessage(),
                'original_error' => $this->originalException ? $this->originalException->getMessage() : null,
            ]);

            throw $e;
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            'webhook',
            'retry',
            "provider:{$this->provider}",
            "event:{$this->event}",
            "attempt:{$this->attempt}",
        ];
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [
            new \Illuminate\Queue\Middleware\RateLimited('webhooks'),
            new \Illuminate\Queue\Middleware\WithoutOverlapping($this->getOverlappingKey()),
        ];
    }

    /**
     * The unique key used to prevent job overlapping.
     *
     * @return string
     */
    protected function getOverlappingKey()
    {
        return "webhook:{$this->provider}:{$this->event}:" . md5(serialize($this->payload));
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array
     */
    public function backoff()
    {
        // Exponential backoff with jitter
        $baseDelay = config("webhooks.{$this->provider}.retry.delay", 5);
        $maxDelay = config("webhooks.{$this->provider}.retry.max_delay", 60);
        
        $delay = min($baseDelay * pow(2, $this->attempt - 1), $maxDelay);
        
        // Add random jitter (±20%)
        $jitter = $delay * 0.2;
        return [$delay - $jitter, $delay + $jitter];
    }

    /**
     * The number of times the job may be attempted.
     *
     * @return int
     */
    public function tries()
    {
        return config("webhooks.{$this->provider}.retry.max_attempts", 3);
    }

    /**
     * Get the retry delay for the job.
     *
     * @return int
     */
    public function retryAfter()
    {
        return config("webhooks.{$this->provider}.retry.delay", 5) * 60;
    }

    /**
     * Determine if the job should be encrypted.
     *
     * @return bool
     */
    public function shouldBeEncrypted()
    {
        return config("webhooks.{$this->provider}.encrypt_payload", true);
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error("Webhook retry job failed: {$this->provider}.{$this->event}", [
            'attempt' => $this->attempt,
            'error' => $exception->getMessage(),
            'original_error' => $this->originalException ? $this->originalException->getMessage() : null,
        ]);

        // Check if we should send an alert
        if ($this->shouldSendFailureAlert()) {
            $this->sendFailureAlert($exception);
        }
    }

    /**
     * Determine if we should send a failure alert.
     *
     * @return bool
     */
    protected function shouldSendFailureAlert()
    {
        return $this->attempt >= config("webhooks.{$this->provider}.retry.alert_after_attempts", 2);
    }

    /**
     * Send a failure alert.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    protected function sendFailureAlert(\Throwable $exception)
    {
        $notification = new \App\Notifications\WebhookRetryFailure(
            $this->provider,
            $this->event,
            $this->attempt,
            $exception,
            $this->originalException
        );

        $recipients = config("webhooks.{$this->provider}.monitoring.alert_recipients", []);
        foreach ($recipients as $recipient) {
            \Illuminate\Support\Facades\Notification::route('mail', $recipient)
                ->notify($notification);
        }
    }
}
