<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyFirebaseWebhook extends BaseWebhookMiddleware
{
    /**
     * Get the webhook provider name.
     *
     * @return string
     */
    protected function getProvider(): string
    {
        return 'firebase';
    }

    /**
     * Verify the webhook signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function verifySignature(Request $request): bool
    {
        if (!config('webhooks.firebase.verify_signature', true)) {
            return true;
        }

        $signature = $request->header('X-Firebase-Webhook-Signature');
        $secret = config('webhooks.firebase.secret');

        if (!$signature || !$secret) {
            Log::warning('Firebase webhook signature verification failed: Missing signature or secret');
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        $result = hash_equals($expectedSignature, $signature);
        
        if (!$result) {
            Log::warning('Firebase webhook signature verification failed: Invalid signature', [
                'expected' => $expectedSignature,
                'received' => $signature
            ]);
        }

        return $result;
    }

    /**
     * Get the webhook event name from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function getEventName(Request $request): ?string
    {
        $payload = $request->all();
        $event = $payload['event'] ?? null;

        // Validate event type
        $validEvents = config('webhooks.firebase.events', []);
        if (!in_array($event, $validEvents)) {
            Log::warning('Invalid Firebase webhook event', [
                'event' => $event,
                'valid_events' => $validEvents
            ]);
            return null;
        }

        return $event;
    }

    /**
     * Additional validation specific to Firebase webhooks.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function validateHeaders(Request $request): bool
    {
        if (!parent::validateHeaders($request)) {
            return false;
        }

        // Firebase-specific header requirements
        $requiredHeaders = [
            'X-Firebase-Webhook-Signature',
            'X-Firebase-Event',
        ];

        foreach ($requiredHeaders as $header) {
            if (!$request->hasHeader($header)) {
                Log::warning('Missing required Firebase webhook header', [
                    'header' => $header
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Additional IP validation specific to Firebase.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function validateIpAddress(Request $request): bool
    {
        if (!parent::validateIpAddress($request)) {
            return false;
        }

        // Firebase-specific IP validation
        $allowedIps = config('webhooks.firebase.allowed_ips', []);
        if (!empty($allowedIps)) {
            $ip = $request->ip();
            if (!in_array($ip, $allowedIps)) {
                Log::warning('Firebase webhook IP not allowed', [
                    'ip' => $ip,
                    'allowed_ips' => $allowedIps
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Enhanced logging for Firebase webhooks.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function logWebhook(Request $request): void
    {
        parent::logWebhook($request);

        // Additional Firebase-specific logging
        $payload = $request->all();
        Log::info('Firebase webhook details', [
            'event' => $payload['event'] ?? null,
            'firebase_id' => $payload['firebase_id'] ?? null,
            'timestamp' => $payload['timestamp'] ?? null,
            'data' => config('webhooks.global.store_payloads', false) 
                ? ($payload['data'] ?? null) 
                : '[hidden]'
        ]);
    }
}
