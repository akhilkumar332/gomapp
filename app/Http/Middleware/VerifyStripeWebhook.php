<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\WebhookSignature;
use Stripe\Exception\SignatureVerificationException;

class VerifyStripeWebhook extends BaseWebhookMiddleware
{
    /**
     * Get the webhook provider name.
     *
     * @return string
     */
    protected function getProvider(): string
    {
        return 'stripe';
    }

    /**
     * Verify the webhook signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function verifySignature(Request $request): bool
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('webhooks.stripe.secret');

        if (!$signature || !$secret) {
            Log::warning('Stripe webhook signature verification failed: Missing signature or secret');
            return false;
        }

        try {
            $tolerance = config('webhooks.stripe.tolerance', 300);
            WebhookSignature::verifyHeader(
                $payload,
                $signature,
                $secret,
                $tolerance
            );

            return true;
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
                'signature' => $signature
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Stripe webhook verification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Get the webhook event name from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function getEventName(Request $request): ?string
    {
        try {
            $payload = $request->all();
            $event = $payload['type'] ?? null;

            // Validate event type
            $validEvents = config('webhooks.stripe.events', []);
            if (!in_array($event, $validEvents)) {
                Log::warning('Invalid Stripe webhook event', [
                    'event' => $event,
                    'valid_events' => $validEvents
                ]);
                return null;
            }

            return $event;
        } catch (\Exception $e) {
            Log::error('Failed to get Stripe event name', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Additional validation specific to Stripe webhooks.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function validateHeaders(Request $request): bool
    {
        if (!parent::validateHeaders($request)) {
            return false;
        }

        // Stripe-specific header requirements
        $requiredHeaders = [
            'Stripe-Signature',
        ];

        foreach ($requiredHeaders as $header) {
            if (!$request->hasHeader($header)) {
                Log::warning('Missing required Stripe webhook header', [
                    'header' => $header
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Enhanced logging for Stripe webhooks.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function logWebhook(Request $request): void
    {
        parent::logWebhook($request);

        try {
            $payload = $request->all();
            
            // Log specific Stripe event details
            Log::info('Stripe webhook details', [
                'event_id' => $payload['id'] ?? null,
                'event_type' => $payload['type'] ?? null,
                'livemode' => $payload['livemode'] ?? null,
                'api_version' => $payload['api_version'] ?? null,
                'created' => isset($payload['created']) 
                    ? date('Y-m-d H:i:s', $payload['created']) 
                    : null,
                'object_type' => $payload['data']['object']['object'] ?? null,
                'object_id' => $payload['data']['object']['id'] ?? null,
                'data' => config('webhooks.global.store_payloads', false)
                    ? ($payload['data']['object'] ?? null)
                    : '[hidden]'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log Stripe webhook details', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle failed signature verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $message
     * @return void
     */
    protected function handleFailedSignature(Request $request, string $message): void
    {
        Log::warning('Stripe webhook signature verification failed', [
            'message' => $message,
            'signature' => $request->header('Stripe-Signature'),
            'event_id' => $request->input('id'),
            'event_type' => $request->input('type')
        ]);

        // Alert administrators about potential security issues
        if (config('webhooks.stripe.alert_on_signature_failure', true)) {
            // Implementation of alert mechanism (e.g., email, Slack, etc.)
            // This could be handled by a dedicated notification service
        }
    }

    /**
     * Additional request validation specific to Stripe.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function validateRequest(Request $request): bool
    {
        // Verify content type
        if ($request->header('Content-Type') !== 'application/json') {
            Log::warning('Invalid Stripe webhook content type', [
                'content_type' => $request->header('Content-Type')
            ]);
            return false;
        }

        // Verify payload structure
        $payload = $request->all();
        $requiredFields = ['id', 'type', 'data'];
        foreach ($requiredFields as $field) {
            if (!isset($payload[$field])) {
                Log::warning('Missing required Stripe webhook field', [
                    'field' => $field
                ]);
                return false;
            }
        }

        // Verify API version compatibility
        $apiVersion = $payload['api_version'] ?? null;
        $supportedVersions = config('webhooks.stripe.supported_api_versions', []);
        if (!empty($supportedVersions) && !in_array($apiVersion, $supportedVersions)) {
            Log::warning('Unsupported Stripe API version', [
                'version' => $apiVersion,
                'supported_versions' => $supportedVersions
            ]);
            return false;
        }

        return true;
    }
}
