<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use App\Notifications\WebhookHealthAlert;

class WebhookHealthMonitor
{
    /**
     * The cache instance.
     *
     * @var \Illuminate\Cache\Repository
     */
    protected $cache;

    /**
     * The logger instance.
     *
     * @var \Illuminate\Log\Logger
     */
    protected $logger;

    /**
     * Create a new webhook health monitor instance.
     *
     * @param  \Illuminate\Cache\Repository  $cache
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __construct($cache, $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * Check the health of all webhook integrations.
     *
     * @return array
     */
    public function check(): array
    {
        $results = [];

        foreach ($this->getProviders() as $provider) {
            $results[$provider] = $this->checkProvider($provider);
        }

        $this->logResults($results);
        $this->handleAlerts($results);

        return $results;
    }

    /**
     * Get all enabled webhook providers.
     *
     * @return array
     */
    protected function getProviders(): array
    {
        return array_filter([
            'firebase' => config('webhooks.firebase.enabled', false),
            'stripe' => config('webhooks.stripe.enabled', false),
            'twilio' => config('webhooks.twilio.enabled', false),
        ], function ($enabled) {
            return $enabled;
        });
    }

    /**
     * Check the health of a specific provider.
     *
     * @param  string  $provider
     * @return array
     */
    protected function checkProvider(string $provider): array
    {
        $metrics = $this->getProviderMetrics($provider);
        $status = $this->determineProviderStatus($provider, $metrics);
        $lastReceived = $this->getLastReceivedTimestamp($provider);
        $issues = $this->detectIssues($provider, $metrics, $status);

        return [
            'status' => $status,
            'metrics' => $metrics,
            'last_received' => $lastReceived,
            'issues' => $issues,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get metrics for a specific provider.
     *
     * @param  string  $provider
     * @return array
     */
    protected function getProviderMetrics(string $provider): array
    {
        return [
            'total_received' => (int) $this->cache->get("webhook:{$provider}:total", 0),
            'total_failures' => (int) $this->cache->get("webhook:{$provider}:failures", 0),
            'success_rate' => $this->calculateSuccessRate($provider),
            'average_response_time' => $this->getAverageResponseTime($provider),
            'events_per_minute' => $this->calculateEventsPerMinute($provider),
        ];
    }

    /**
     * Calculate the success rate for a provider.
     *
     * @param  string  $provider
     * @return float
     */
    protected function calculateSuccessRate(string $provider): float
    {
        $total = (int) $this->cache->get("webhook:{$provider}:total", 0);
        $failures = (int) $this->cache->get("webhook:{$provider}:failures", 0);

        if ($total === 0) {
            return 100.0;
        }

        return round(100 - (($failures / $total) * 100), 2);
    }

    /**
     * Get the average response time for a provider.
     *
     * @param  string  $provider
     * @return float
     */
    protected function getAverageResponseTime(string $provider): float
    {
        $times = $this->cache->get("webhook:{$provider}:response_times", []);
        
        if (empty($times)) {
            return 0.0;
        }

        return round(array_sum($times) / count($times), 2);
    }

    /**
     * Calculate events per minute for a provider.
     *
     * @param  string  $provider
     * @return float
     */
    protected function calculateEventsPerMinute(string $provider): float
    {
        $key = "webhook:{$provider}:events_per_minute";
        $events = $this->cache->get($key, []);
        
        // Clean up old events
        $events = array_filter($events, function ($timestamp) {
            return $timestamp > now()->subMinutes(5)->timestamp;
        });

        return round(count($events) / 5, 2);
    }

    /**
     * Get the last received timestamp for a provider.
     *
     * @param  string  $provider
     * @return string|null
     */
    protected function getLastReceivedTimestamp(string $provider): ?string
    {
        $timestamp = $this->cache->get("webhook:{$provider}:last_received");
        
        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }

    /**
     * Determine the status of a provider.
     *
     * @param  string  $provider
     * @param  array  $metrics
     * @return string
     */
    protected function determineProviderStatus(string $provider, array $metrics): string
    {
        // Check if we've received any webhooks recently
        $lastReceived = $this->cache->get("webhook:{$provider}:last_received");
        if (!$lastReceived || now()->timestamp - $lastReceived > 3600) {
            return 'inactive';
        }

        // Check success rate
        if ($metrics['success_rate'] < 90) {
            return 'degraded';
        }

        // Check response time
        if ($metrics['average_response_time'] > 1000) {
            return 'slow';
        }

        return 'healthy';
    }

    /**
     * Detect issues for a provider.
     *
     * @param  string  $provider
     * @param  array  $metrics
     * @param  string  $status
     * @return array
     */
    protected function detectIssues(string $provider, array $metrics, string $status): array
    {
        $issues = [];

        // Check for high failure rate
        if ($metrics['success_rate'] < 95) {
            $issues[] = [
                'type' => 'high_failure_rate',
                'message' => "High failure rate detected ({$metrics['success_rate']}%)",
                'severity' => 'high',
            ];
        }

        // Check for slow response time
        if ($metrics['average_response_time'] > 1000) {
            $issues[] = [
                'type' => 'high_latency',
                'message' => "High average response time ({$metrics['average_response_time']}ms)",
                'severity' => 'medium',
            ];
        }

        // Check for inactivity
        $lastReceived = $this->cache->get("webhook:{$provider}:last_received");
        if (!$lastReceived || now()->timestamp - $lastReceived > 3600) {
            $issues[] = [
                'type' => 'inactivity',
                'message' => 'No webhooks received in the last hour',
                'severity' => 'high',
            ];
        }

        return $issues;
    }

    /**
     * Log the health check results.
     *
     * @param  array  $results
     * @return void
     */
    protected function logResults(array $results): void
    {
        foreach ($results as $provider => $result) {
            $this->logger->info("Webhook health check: {$provider}", [
                'provider' => $provider,
                'status' => $result['status'],
                'metrics' => $result['metrics'],
                'issues' => $result['issues'],
            ]);
        }
    }

    /**
     * Handle alerts based on health check results.
     *
     * @param  array  $results
     * @return void
     */
    protected function handleAlerts(array $results): void
    {
        foreach ($results as $provider => $result) {
            if ($result['status'] !== 'healthy' && !empty($result['issues'])) {
                $this->sendAlert($provider, $result);
            }
        }
    }

    /**
     * Send an alert for a provider.
     *
     * @param  string  $provider
     * @param  array  $result
     * @return void
     */
    protected function sendAlert(string $provider, array $result): void
    {
        // Check if we should send an alert
        $key = "webhook:alert:{$provider}";
        if ($this->cache->has($key)) {
            return;
        }

        // Send the alert
        try {
            $channels = config("webhooks.{$provider}.monitoring.notification_channels", ['mail']);
            $recipients = config("webhooks.{$provider}.monitoring.alert_recipients", []);

            foreach ($recipients as $recipient) {
                Notification::route('mail', $recipient)
                    ->notify(new WebhookHealthAlert($provider, $result));
            }

            // Prevent alert spam
            $interval = config("webhooks.{$provider}.monitoring.alert_interval", 60);
            $this->cache->put($key, true, now()->addMinutes($interval));

        } catch (\Exception $e) {
            $this->logger->error("Failed to send webhook health alert", [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
