<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\FirebaseService;
use Laravel\Cashier\Http\Controllers\WebhookController as StripeWebhookController;
use Stripe\Event as StripeEvent;

class WebhookController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Handle Firebase webhook events
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleFirebase(Request $request)
    {
        try {
            // Verify Firebase webhook signature
            if (!$this->verifyFirebaseWebhook($request)) {
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $payload = $request->all();
            $event = $payload['event'] ?? null;

            Log::info('Firebase webhook received', [
                'event' => $event,
                'payload' => $payload
            ]);

            switch ($event) {
                case 'user.created':
                    return $this->handleFirebaseUserCreated($payload);
                case 'user.deleted':
                    return $this->handleFirebaseUserDeleted($payload);
                case 'phone.verified':
                    return $this->handleFirebasePhoneVerified($payload);
                default:
                    return response()->json(['message' => 'Event not handled'], 202);
            }
        } catch (\Exception $e) {
            Log::error('Firebase webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle Stripe webhook events
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleStripe(Request $request)
    {
        try {
            $payload = $request->all();
            $event = StripeEvent::constructFrom($payload);

            Log::info('Stripe webhook received', [
                'type' => $event->type,
                'object' => $event->data->object->id
            ]);

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    return $this->handleStripePaymentSucceeded($event);
                case 'payment_intent.payment_failed':
                    return $this->handleStripePaymentFailed($event);
                case 'customer.subscription.updated':
                    return $this->handleStripeSubscriptionUpdated($event);
                case 'customer.subscription.deleted':
                    return $this->handleStripeSubscriptionDeleted($event);
                default:
                    return response()->json(['message' => 'Event not handled'], 202);
            }
        } catch (\Exception $e) {
            Log::error('Stripe webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle Twilio webhook events
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleTwilio(Request $request)
    {
        try {
            // Verify Twilio webhook signature
            if (!$this->verifyTwilioWebhook($request)) {
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $payload = $request->all();
            $event = $payload['EventType'] ?? null;

            Log::info('Twilio webhook received', [
                'event' => $event,
                'payload' => $payload
            ]);

            switch ($event) {
                case 'MessageStatus':
                    return $this->handleTwilioMessageStatus($payload);
                case 'CallStatus':
                    return $this->handleTwilioCallStatus($payload);
                default:
                    return response()->json(['message' => 'Event not handled'], 202);
            }
        } catch (\Exception $e) {
            Log::error('Twilio webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Verify Firebase webhook signature
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function verifyFirebaseWebhook(Request $request)
    {
        $signature = $request->header('X-Firebase-Webhook-Signature');
        $secret = config('services.firebase.webhook_secret');

        if (!$signature || !$secret) {
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify Twilio webhook signature
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function verifyTwilioWebhook(Request $request)
    {
        $signature = $request->header('X-Twilio-Signature');
        $secret = config('services.twilio.auth_token');

        if (!$signature || !$secret) {
            return false;
        }

        $url = $request->fullUrl();
        $data = $request->toArray();

        // Sort the data array by key
        ksort($data);

        // Generate the validation string
        $validationString = $url;
        foreach ($data as $key => $value) {
            $validationString .= $key . $value;
        }

        $expectedSignature = base64_encode(hash_hmac('sha1', $validationString, $secret, true));

        return hash_equals($expectedSignature, $signature);
    }

    // Firebase webhook handlers
    protected function handleFirebaseUserCreated($payload)
    {
        // Implementation
        return response()->json(['message' => 'Firebase user created event processed']);
    }

    protected function handleFirebaseUserDeleted($payload)
    {
        // Implementation
        return response()->json(['message' => 'Firebase user deleted event processed']);
    }

    protected function handleFirebasePhoneVerified($payload)
    {
        // Implementation
        return response()->json(['message' => 'Firebase phone verified event processed']);
    }

    // Stripe webhook handlers
    protected function handleStripePaymentSucceeded($event)
    {
        // Implementation
        return response()->json(['message' => 'Stripe payment succeeded event processed']);
    }

    protected function handleStripePaymentFailed($event)
    {
        // Implementation
        return response()->json(['message' => 'Stripe payment failed event processed']);
    }

    protected function handleStripeSubscriptionUpdated($event)
    {
        // Implementation
        return response()->json(['message' => 'Stripe subscription updated event processed']);
    }

    protected function handleStripeSubscriptionDeleted($event)
    {
        // Implementation
        return response()->json(['message' => 'Stripe subscription deleted event processed']);
    }

    // Twilio webhook handlers
    protected function handleTwilioMessageStatus($payload)
    {
        // Implementation
        return response()->json(['message' => 'Twilio message status event processed']);
    }

    protected function handleTwilioCallStatus($payload)
    {
        // Implementation
        return response()->json(['message' => 'Twilio call status event processed']);
    }
}
