<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;

class WebhookHealthAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $provider;
    protected $result;

    public function __construct(string $provider, array $result)
    {
        $this->provider = $provider;
        $this->result = $result;
    }

    public function via($notifiable)
    {
        return config("webhooks.{$this->provider}.monitoring.notification_channels", ['mail']);
    }

    public function toMail($notifiable)
    {
        $lastReceived = isset($this->result['last_received']) ? $this->result['last_received'] : 'N/A';

        $message = (new MailMessage)
            ->subject("⚠️ Webhook Health Alert: {$this->provider}")
            ->greeting("Webhook Health Issue Detected")
            ->line("The {$this->provider} webhook integration is currently experiencing issues.")
            ->line("Status: {$this->result['status']}")
            ->line("Last Received: {$lastReceived}");

        if (!empty($this->result['metrics'])) {
            $message->line('Metrics:');
            foreach ($this->result['metrics'] as $key => $value) {
                $message->line("- " . ucwords(str_replace('_', ' ', $key)) . ": {$value}");
            }
        }

        if (!empty($this->result['issues'])) {
            $message->line('Detected Issues:');
            foreach ($this->result['issues'] as $issue) {
                $message->line("- [{$issue['severity']}] {$issue['message']}");
            }
        }

        if (config('webhooks.monitoring.dashboard_url')) {
            $message->action(
                'View Dashboard',
                config('webhooks.monitoring.dashboard_url')
            );
        }

        $alertInterval = config("webhooks.{$this->provider}.monitoring.alert_interval", 60);

        return $message
            ->line('Please investigate and take necessary action.')
            ->line("This alert will not repeat for {$alertInterval} minutes.");
    }

    public function toSlack($notifiable)
    {
        $lastReceived = isset($this->result['last_received']) ? $this->result['last_received'] : 'N/A';

        $message = (new SlackMessage)
            ->error()
            ->content("⚠️ Webhook Health Alert: {$this->provider}")
            ->attachment(function ($attachment) use ($lastReceived) {
                $attachment
                    ->title('Health Check Details')
                    ->fields([
                        'Status' => $this->result['status'],
                        'Last Received' => $lastReceived,
                        'Success Rate' => $this->result['metrics']['success_rate'] . '%',
                        'Events/Minute' => $this->result['metrics']['events_per_minute'],
                        'Avg Response Time' => $this->result['metrics']['average_response_time'] . 'ms',
                    ]);

                if (!empty($this->result['issues'])) {
                    foreach ($this->result['issues'] as $issue) {
                        $attachment->field(
                            "Issue ({$issue['severity']})",
                            $issue['message']
                        );
                    }
                }
            });

        if (config('webhooks.monitoring.dashboard_url')) {
            $message->attachment(function ($attachment) {
                $attachment
                    ->title('View Dashboard')
                    ->titleLink(config('webhooks.monitoring.dashboard_url'));
            });
        }

        return $message;
    }

    public function toArray($notifiable)
    {
        return [
            'provider' => $this->provider,
            'status' => $this->result['status'],
            'metrics' => $this->result['metrics'],
            'issues' => $this->result['issues'],
            'last_received' => isset($this->result['last_received']) ? $this->result['last_received'] : null,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function tags()
    {
        return [
            'webhook',
            'health_alert',
            "provider:{$this->provider}",
            "status:{$this->result['status']}",
        ];
    }

    public function withDelay()
    {
        return [
            'mail' => now()->addSeconds(30),
            'slack' => now()->addSeconds(10),
        ];
    }

    public function retryUntil()
    {
        return now()->addHours(12);
    }

    public function retryAfter()
    {
        return [
            'mail' => 300,
            'slack' => 60,
        ];
    }

    public function maxRetries()
    {
        return 3;
    }
}
