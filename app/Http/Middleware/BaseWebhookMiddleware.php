<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Exceptions\WebhookException;

abstract class BaseWebhookMiddleware
{
    /**
     * Handle an incoming webhook request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Check if webhooks are enabled
            if (!$this->isEnabled()) {
                throw new WebhookException('Webhooks are disabled', 503);
            }

            // Validate request size
            if (!$this->validateRequestSize($request)) {
                throw new WebhookException('Request payload too large', 413);
            }

            // Validate required headers
            if (!$this->validateHeaders($request)) {
                throw new WebhookException('Missing required headers', 400);
            }

            // Check IP restrictions
            if (!$this->validateIpAddress($request)) {
                throw new WebhookException('IP address not allowed', 403);
            }

            // Verify webhook signature
            if (!$this->verifySignature($request)) {
                throw new WebhookException('Invalid webhook signature', 401);
            }

            // Log the webhook request
            $this->logWebhook($request);

            // Process the webhook
            return $next($request);
        } catch (WebhookException $e) {
            return $this->handleException($e, $request);
        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'provider' => $this->getProvider(),
                'request' => [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'headers' => $request->headers->all(),
                    'ip' => $request->ip(),
                ]
            ]);

            return response()->json([
                'error' => 'Webhook processing failed',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Check if webhooks are enabled for this provider.
     *
     * @return bool
     */
    protected function isEnabled(): bool
    {
        return config("webhooks.{$this->getProvider()}.enabled", false);
    }

    /**
     * Validate the request payload size.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function validateRequestSize(Request $request): bool
    {
        $maxSize = config('webhooks.global.max_payload_size', 10 * 1024 * 1024); // 10MB default
        return $request->header('Content-Length', 0) <= $maxSize;
    }

    /**
     * Validate required headers are present.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function validateHeaders(Request $request): bool
    {
        $requiredHeaders = config('webhooks.global.required_headers', []);
        foreach ($requiredHeaders as $header) {
            if (!$request->hasHeader($header)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate the request IP address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function validateIpAddress(Request $request): bool
    {
        if (!config('webhooks.global.ip_filtering.enabled', false)) {
            return true;
        }

        $ip = $request->ip();
        $provider = $this->getProvider();

        // Check provider-specific allowed IPs
        $allowedIps = config("webhooks.{$provider}.allowed_ips", []);
        if (!empty($allowedIps) && !in_array($ip, $allowedIps)) {
            return false;
        }

        // Check global allowed IPs
        $globalAllowedIps = config('webhooks.global.ip_filtering.allowed_ips', []);
        if (!empty($globalAllowedIps) && !in_array($ip, $globalAllowedIps)) {
            return false;
        }

        // Check global blocked IPs
        $blockedIps = config('webhooks.global.ip_filtering.blocked_ips', []);
        if (in_array($ip, $blockedIps)) {
            return false;
        }

        return true;
    }

    /**
     * Log the webhook request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function logWebhook(Request $request): void
    {
        $provider = $this->getProvider();
        $event = $this->getEventName($request);

        Log::info("Webhook received from {$provider}", [
            'provider' => $provider,
            'event' => $event,
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'payload' => config('webhooks.global.store_payloads', false) ? $request->all() : '[hidden]'
        ]);
    }

    /**
     * Handle webhook exceptions.
     *
     * @param  \Exception  $e
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function handleException(\Exception $e, Request $request)
    {
        Log::warning('Webhook validation failed', [
            'error' => $e->getMessage(),
            'provider' => $this->getProvider(),
            'ip' => $request->ip(),
            'headers' => $request->headers->all()
        ]);

        return response()->json([
            'error' => 'Webhook validation failed',
            'message' => config('app.debug') ? $e->getMessage() : 'Invalid webhook request'
        ], $e->getCode() ?: 400);
    }

    /**
     * Get the webhook provider name.
     *
     * @return string
     */
    abstract protected function getProvider(): string;

    /**
     * Verify the webhook signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    abstract protected function verifySignature(Request $request): bool;

    /**
     * Get the webhook event name from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    abstract protected function getEventName(Request $request): ?string;
}
