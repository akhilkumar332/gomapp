<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;

class WebhookRetryFailure extends Notification implements ShouldQueue
{
    use Queueable;

    protected $provider;
    protected $event;
    protected $attempt;
    protected $exception;
    protected $originalException;

    public function __construct(
        string $provider,
        string $event,
        int $attempt,
        \Throwable $exception,
        ?\Throwable $originalException = null
    ) {
        $this->provider = $provider;
        $this->event = $event;
        $this->attempt = $attempt;
        $this->exception = $exception;
        $this->originalException = $originalException;
    }

    public function via($notifiable)
    {
        return config("webhooks.{$this->provider}.monitoring.notification_channels", ['mail']);
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->error()
            ->subject("🚨 Webhook Retry Failed: {$this->provider}")
            ->greeting('Webhook Retry Failure Alert')
            ->line("A webhook retry attempt has failed for {$this->provider}.")
            ->line("Event: {$this->event}")
            ->line("Attempt: {$this->attempt} of " . config("webhooks.{$this->provider}.retry.max_attempts", 3));

        // Add error details
        $message->line('Error Details:')
            ->line("Message: {$this->exception->getMessage()}")
            ->line("Location: {$this->exception->getFile()}:{$this->exception->getLine()}");

        // Add original error if available
        if ($this->originalException) {
            $message->line('Original Error:')
                ->line("Message: {$this->originalException->getMessage()}")
                ->line("Location: {$this->originalException->getFile()}:{$this->originalException->getLine()}");
        }

        // Add dashboard link if available
        if (config('webhooks.monitoring.dashboard_url')) {
            $message->action(
                'View Dashboard',
                config('webhooks.monitoring.dashboard_url')
            );
        }

        // Add support contact if available
        if ($supportEmail = config('webhooks.monitoring.support_email')) {
            $message->line('')
                ->line("For urgent assistance, please contact:")
                ->line($supportEmail);
        }

        return $message;
    }

    public function toSlack($notifiable)
    {
        $message = (new SlackMessage)
            ->error()
            ->content("🚨 Webhook Retry Failed: {$this->provider}")
            ->attachment(function ($attachment) {
                $attachment
                    ->title('Retry Failure Details')
                    ->fields([
                        'Provider' => $this->provider,
                        'Event' => $this->event,
                        'Attempt' => "{$this->attempt} of " . config("webhooks.{$this->provider}.retry.max_attempts", 3),
                        'Error' => $this->exception->getMessage(),
                        'Location' => "{$this->exception->getFile()}:{$this->exception->getLine()}",
                    ]);

                if ($this->originalException) {
                    $attachment->fields([
                        'Original Error' => $this->originalException->getMessage(),
                        'Original Location' => "{$this->originalException->getFile()}:{$this->originalException->getLine()}",
                    ]);
                }
            });

        // Add dashboard link if available
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
            'event' => $this->event,
            'attempt' => $this->attempt,
            'max_attempts' => config("webhooks.{$this->provider}.retry.max_attempts", 3),
            'error' => [
                'message' => $this->exception->getMessage(),
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
            ],
            'original_error' => $this->originalException ? [
                'message' => $this->originalException->getMessage(),
                'file' => $this->originalException->getFile(),
                'line' => $this->originalException->getLine(),
            ] : null,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function tags()
    {
        return [
            'webhook',
            'retry_failure',
            "provider:{$this->provider}",
            "event:{$this->event}",
            "attempt:{$this->attempt}",
        ];
    }

    public function shouldSend($notifiable)
    {
        // Check if we should throttle notifications
        $key = "webhook:retry_failure:{$this->provider}:{$this->event}";
        $throttle = config("webhooks.{$this->provider}.monitoring.notification_throttle", 5);

        if (cache()->has($key)) {
            $count = cache()->increment($key);
            return $count <= $throttle;
        }

        cache()->put($key, 1, now()->addHour());
        return true;
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
        return now()->addHours(1);
    }
}
