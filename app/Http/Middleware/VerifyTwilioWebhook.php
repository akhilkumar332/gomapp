<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Twilio\Security\RequestValidator;

class VerifyTwilioWebhook extends BaseWebhookMiddleware
{
    /**
     * Get the webhook provider name.
     *
     * @return string
     */
    protected function getProvider(): string
    {
        return 'twilio';
    }

    /**
     * Verify the webhook signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function verifySignature(Request $request): bool
    {
        if (!config('webhooks.twilio.verify_signature', true)) {
            return true;
        }

        $signature = $request->header('X-Twilio-Signature');
        $authToken = config('webhooks.twilio.auth_token');

        if (!$signature || !$authToken) {
            Log::warning('Twilio webhook signature verification failed: Missing signature or auth token');
            return false;
        }

        try {
            $validator = new RequestValidator($authToken);
            $url = $request->fullUrl();
            $params = $request->toArray();

            // Sort parameters for consistent validation
            ksort($params);

            $isValid = $validator->validate(
                $signature,
                $url,
                $params
            );

            if (!$isValid) {
                Log::warning('Twilio webhook signature verification failed', [
                    'url' => $url,
                    'signature' => $signature
                ]);
            }

            return $isValid;
        } catch (\Exception $e) {
            Log::error('Twilio webhook verification error', [
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
        $event = $request->input('EventType');

        // Validate event type
        $validEvents = config('webhooks.twilio.events', []);
        if (!in_array($event, $validEvents)) {
            Log::warning('Invalid Twilio webhook event', [
                'event' => $event,
                'valid_events' => $validEvents
            ]);
            return null;
        }

        return $event;
    }

    /**
     * Additional validation specific to Twilio webhooks.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function validateHeaders(Request $request): bool
    {
        if (!parent::validateHeaders($request)) {
            return false;
        }

        // Twilio-specific header requirements
        $requiredHeaders = [
            'X-Twilio-Signature',
        ];

        foreach ($requiredHeaders as $header) {
            if (!$request->hasHeader($header)) {
                Log::warning('Missing required Twilio webhook header', [
                    'header' => $header
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Additional validation for Twilio webhooks.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function validateRequest(Request $request): bool
    {
        // Verify content type
        $contentType = $request->header('Content-Type');
        if ($contentType !== 'application/x-www-form-urlencoded') {
            Log::warning('Invalid Twilio webhook content type', [
                'content_type' => $contentType
            ]);
            return false;
        }

        // Verify required parameters based on event type
        $event = $request->input('EventType');
        $requiredParams = $this->getRequiredParameters($event);
        
        foreach ($requiredParams as $param) {
            if (!$request->has($param)) {
                Log::warning('Missing required Twilio webhook parameter', [
                    'event' => $event,
                    'parameter' => $param
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Get required parameters for each event type.
     *
     * @param  string|null  $event
     * @return array
     */
    protected function getRequiredParameters(?string $event): array
    {
        $baseParams = ['AccountSid', 'EventType'];

        switch ($event) {
            case 'MessageStatus':
                return array_merge($baseParams, [
                    'MessageSid',
                    'MessageStatus',
                    'To',
                    'From'
                ]);

            case 'CallStatus':
                return array_merge($baseParams, [
                    'CallSid',
                    'CallStatus',
                    'To',
                    'From'
                ]);

            default:
                return $baseParams;
        }
    }

    /**
     * Enhanced logging for Twilio webhooks.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function logWebhook(Request $request): void
    {
        parent::logWebhook($request);

        // Additional Twilio-specific logging
        $event = $request->input('EventType');
        $logData = [
            'event' => $event,
            'account_sid' => $request->input('AccountSid'),
            'timestamp' => now()->toIso8601String(),
        ];

        // Add event-specific data
        switch ($event) {
            case 'MessageStatus':
                $logData += [
                    'message_sid' => $request->input('MessageSid'),
                    'status' => $request->input('MessageStatus'),
                    'to' => $request->input('To'),
                    'from' => $request->input('From'),
                    'error_code' => $request->input('ErrorCode'),
                    'error_message' => $request->input('ErrorMessage'),
                ];
                break;

            case 'CallStatus':
                $logData += [
                    'call_sid' => $request->input('CallSid'),
                    'status' => $request->input('CallStatus'),
                    'to' => $request->input('To'),
                    'from' => $request->input('From'),
                    'duration' => $request->input('CallDuration'),
                    'recording_url' => $request->input('RecordingUrl'),
                ];
                break;
        }

        Log::info('Twilio webhook details', $logData);
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
        Log::warning('Twilio webhook signature verification failed', [
            'message' => $message,
            'signature' => $request->header('X-Twilio-Signature'),
            'account_sid' => $request->input('AccountSid'),
            'event_type' => $request->input('EventType')
        ]);

        // Alert administrators about potential security issues
        if (config('webhooks.twilio.alert_on_signature_failure', true)) {
            // Implementation of alert mechanism (e.g., email, Slack, etc.)
            // This could be handled by a dedicated notification service
        }
    }
}
