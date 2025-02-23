<?php

namespace App\Services;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Log;
use App\Exceptions\WebhookException;

class WebhookEventDispatcher
{
    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * Create a new webhook event dispatcher instance.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    /**
     * Dispatch a webhook event.
     *
     * @param  string  $provider
     * @param  string  $event
     * @param  array  $payload
     * @return void
     *
     * @throws \App\Exceptions\WebhookException
     */
    public function dispatch(string $provider, string $event, array $payload): void
    {
        try {
            // Validate the provider and event
            $this->validateProvider($provider);
            $this->validateEvent($provider, $event);

            // Log the incoming webhook
            $this->logIncomingWebhook($provider, $event, $payload);

            // Dispatch the webhook received event
            $this->events->dispatch('webhook.received', [$provider, $event, $payload]);

            // Dispatch the provider-specific event
            $eventName = "webhook.{$provider}.{$event}";
            $this->events->dispatch($eventName, [$payload]);

            // Process the webhook using the appropriate handler
            $this->processWebhook($provider, $event, $payload);

        } catch (\Exception $e) {
            $this->handleWebhookError($provider, $event, $e);
            throw $e;
        }
    }

    /**
     * Validate the webhook provider.
     *
     * @param  string  $provider
     * @return void
     *
     * @throws \App\Exceptions\WebhookException
     */
    protected function validateProvider(string $provider): void
    {
        if (!config("webhooks.{$provider}.enabled", false)) {
            throw new WebhookException(
                "Webhook provider '{$provider}' is not enabled",
                400,
                null,
                $provider
            );
        }
    }

    /**
     * Validate the webhook event.
     *
     * @param  string  $provider
     * @param  string  $event
     * @return void
     *
     * @throws \App\Exceptions\WebhookException
     */
    protected function validateEvent(string $provider, string $event): void
    {
        $validEvents = config("webhooks.{$provider}.events", []);

        if (!empty($validEvents) && !in_array($event, $validEvents)) {
            throw new WebhookException(
                "Invalid webhook event '{$event}' for provider '{$provider}'",
                400,
                null,
                $provider,
                $event
            );
        }
    }

    /**
     * Process the webhook using the appropriate handler.
     *
     * @param  string  $provider
     * @param  string  $event
     * @param  array  $payload
     * @return void
     *
     * @throws \App\Exceptions\WebhookException
     */
    protected function processWebhook(string $provider, string $event, array $payload): void
    {
        $handlerClass = config("webhooks.handlers.{$provider}.{$event}");

        if (!$handlerClass || !class_exists($handlerClass)) {
            Log::warning("No handler found for webhook {$provider}.{$event}");
            return;
        }

        try {
            $handler = app($handlerClass);
            $handler->handle($payload);
        } catch (\Exception $e) {
            throw new WebhookException(
                "Failed to process webhook: {$e->getMessage()}",
                500,
                $e,
                $provider,
                $event
            );
        }
    }

    /**
     * Log the incoming webhook.
     *
     * @param  string  $provider
     * @param  string  $event
     * @param  array  $payload
     * @return void
     */
    protected function logIncomingWebhook(string $provider, string $event, array $payload): void
    {
        $shouldStorePayload = config('webhooks.global.store_payloads', false);

        Log::channel('webhook')->info("Incoming webhook: {$provider}.{$event}", [
            'provider' => $provider,
            'event' => $event,
            'payload' => $shouldStorePayload ? $payload : '[hidden]',
            'timestamp' => now()->toIso8601String(),
            'request_id' => request()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle a webhook error.
     *
     * @param  string  $provider
     * @param  string  $event
     * @param  \Exception  $error
     * @return void
     */
    protected function handleWebhookError(string $provider, string $event, \Exception $error): void
    {
        // Log the error
        Log::channel('webhook')->error("Webhook error: {$provider}.{$event}", [
            'provider' => $provider,
            'event' => $event,
            'error' => $error->getMessage(),
            'trace' => $error->getTraceAsString(),
            'timestamp' => now()->toIso8601String(),
            'request_id' => request()->id(),
        ]);

        // Dispatch the webhook failed event
        $this->events->dispatch('webhook.failed', [$provider, $event, $error]);

        // Check if we should retry
        $this->handleRetry($provider, $event, $error);
    }

    /**
     * Handle webhook retry logic.
     *
     * @param  string  $provider
     * @param  string  $event
     * @param  \Exception  $error
     * @return void
     */
    protected function handleRetry(string $provider, string $event, \Exception $error): void
    {
        $retryConfig = config("webhooks.{$provider}.retry", [
            'max_attempts' => 3,
            'delay' => 5
        ]);

        $key = "webhook_retry:{$provider}:{$event}:" . request()->id();
        $attempts = cache()->increment($key);

        if ($attempts <= $retryConfig['max_attempts']) {
            // Schedule a retry
            $job = new \App\Jobs\ProcessWebhookRetry(
                $provider,
                $event,
                request()->all(),
                $attempts
            );

            $job->delay(now()->addMinutes($retryConfig['delay']));
            dispatch($job);

            Log::info("Scheduled webhook retry: {$provider}.{$event}", [
                'attempt' => $attempts,
                'next_retry' => now()->addMinutes($retryConfig['delay']),
            ]);
        } else {
            // Max retries reached
            Log::warning("Max retry attempts reached for webhook: {$provider}.{$event}", [
                'max_attempts' => $retryConfig['max_attempts'],
            ]);

            cache()->forget($key);
        }
    }
}
