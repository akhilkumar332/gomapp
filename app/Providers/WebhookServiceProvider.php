<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WebhookServiceProvider extends ServiceProvider
{
    /**
     * Register any webhook services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/webhooks.php', 'webhooks'
        );

        // Register webhook event listeners
        $this->app->singleton('webhook.dispatcher', function ($app) {
            return new WebhookEventDispatcher($app['events']);
        });
    }

    /**
     * Bootstrap any webhook services.
     */
    public function boot(): void
    {
        // Register webhook events
        $this->registerWebhookEvents();

        // Register webhook health checks
        $this->registerWebhookHealthChecks();

        // Register webhook cleanup tasks
        $this->registerWebhookCleanup();
    }

    /**
     * Register webhook events and listeners.
     */
    protected function registerWebhookEvents(): void
    {
        // Firebase Events
        Event::listen('webhook.firebase.user.created', function ($payload) {
            $this->logWebhookEvent('firebase', 'user.created', $payload);
        });

        Event::listen('webhook.firebase.user.deleted', function ($payload) {
            $this->logWebhookEvent('firebase', 'user.deleted', $payload);
        });

        // Stripe Events
        Event::listen('webhook.stripe.payment.succeeded', function ($payload) {
            $this->logWebhookEvent('stripe', 'payment.succeeded', $payload);
        });

        Event::listen('webhook.stripe.payment.failed', function ($payload) {
            $this->logWebhookEvent('stripe', 'payment.failed', $payload);
        });

        // Twilio Events
        Event::listen('webhook.twilio.message.status', function ($payload) {
            $this->logWebhookEvent('twilio', 'message.status', $payload);
        });

        Event::listen('webhook.twilio.call.status', function ($payload) {
            $this->logWebhookEvent('twilio', 'call.status', $payload);
        });

        // Generic webhook events
        Event::listen('webhook.received', function ($provider, $event, $payload) {
            $this->logWebhookEvent($provider, $event, $payload);
        });

        Event::listen('webhook.failed', function ($provider, $event, $error) {
            $this->logWebhookFailure($provider, $event, $error);
        });
    }

    /**
     * Register webhook health checks.
     */
    protected function registerWebhookHealthChecks(): void
    {
        $this->app->singleton('webhook.health', function ($app) {
            return new WebhookHealthMonitor(
                $app['cache'],
                $app['log']
            );
        });

        // Register health check schedule
        if ($this->app->runningInConsole()) {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            
            $schedule->call(function () {
                $this->app['webhook.health']->check();
            })->everyFiveMinutes();
        }
    }

    /**
     * Register webhook cleanup tasks.
     */
    protected function registerWebhookCleanup(): void
    {
        // Clean up old webhook logs
        if ($this->app->runningInConsole()) {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            
            $schedule->call(function () {
                $retention = config('webhooks.global.log_retention', 30);
                $this->cleanupWebhookLogs($retention);
            })->daily();
        }
    }

    /**
     * Log a webhook event.
     */
    protected function logWebhookEvent(string $provider, string $event, array $payload): void
    {
        if (!config("webhooks.{$provider}.enabled", true)) {
            return;
        }

        $shouldStorePayload = config('webhooks.global.store_payloads', false);
        
        Log::channel('webhook')->info("Webhook received: {$provider}.{$event}", [
            'provider' => $provider,
            'event' => $event,
            'payload' => $shouldStorePayload ? $payload : '[hidden]',
            'timestamp' => now()->toIso8601String(),
            'request_id' => request()->id(),
        ]);

        // Update webhook metrics
        $this->updateWebhookMetrics($provider, $event);
    }

    /**
     * Log a webhook failure.
     */
    protected function logWebhookFailure(string $provider, string $event, \Throwable $error): void
    {
        Log::channel('webhook')->error("Webhook failed: {$provider}.{$event}", [
            'provider' => $provider,
            'event' => $event,
            'error' => $error->getMessage(),
            'trace' => $error->getTraceAsString(),
            'timestamp' => now()->toIso8601String(),
            'request_id' => request()->id(),
        ]);

        // Update failure metrics
        $this->updateWebhookFailureMetrics($provider, $event);

        // Check if we should send alerts
        $this->checkFailureThresholds($provider);
    }

    /**
     * Update webhook metrics.
     */
    protected function updateWebhookMetrics(string $provider, string $event): void
    {
        $metrics = [
            "webhook:{$provider}:total" => 1,
            "webhook:{$provider}:{$event}:total" => 1,
            "webhook:{$provider}:last_received" => now()->timestamp,
        ];

        foreach ($metrics as $key => $value) {
            Cache::increment($key, $value);
        }
    }

    /**
     * Update webhook failure metrics.
     */
    protected function updateWebhookFailureMetrics(string $provider, string $event): void
    {
        $metrics = [
            "webhook:{$provider}:failures" => 1,
            "webhook:{$provider}:{$event}:failures" => 1,
        ];

        foreach ($metrics as $key => $value) {
            Cache::increment($key, $value);
        }
    }

    /**
     * Check failure thresholds and send alerts if necessary.
     */
    protected function checkFailureThresholds(string $provider): void
    {
        $threshold = config("webhooks.{$provider}.monitoring.alert_threshold", 5);
        $interval = config("webhooks.{$provider}.monitoring.alert_interval", 60);
        
        $key = "webhook:{$provider}:failures";
        $failures = Cache::get($key, 0);

        if ($failures >= $threshold) {
            $this->sendFailureAlert($provider, $failures);
            
            // Reset the counter
            Cache::put($key, 0, now()->addMinutes($interval));
        }
    }

    /**
     * Send a failure alert.
     */
    protected function sendFailureAlert(string $provider, int $failures): void
    {
        $channels = config("webhooks.{$provider}.monitoring.notification_channels", ['mail']);

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
                }
            } catch (\Exception $e) {
                Log::error("Failed to send webhook alert via {$channel}", [
                    'provider' => $provider,
                    'failures' => $failures,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Clean up old webhook logs.
     */
    protected function cleanupWebhookLogs(int $days): void
    {
        try {
            $date = now()->subDays($days);
            
            // Clean up database logs
            \DB::table('webhook_logs')
                ->where('created_at', '<', $date)
                ->delete();

            // Clean up file logs
            $logPath = storage_path('logs/webhook');
            if (is_dir($logPath)) {
                foreach (glob("{$logPath}/*.log") as $file) {
                    if (filemtime($file) < $date->timestamp) {
                        unlink($file);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to cleanup webhook logs', [
                'error' => $e->getMessage(),
                'days' => $days,
            ]);
        }
    }
}
