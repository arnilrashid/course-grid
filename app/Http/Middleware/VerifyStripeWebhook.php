<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;

class VerifyStripeWebhook
{
    /**
     * Verify the Stripe webhook signature.
     *
     * Uses the raw request body and the Stripe-Signature header to validate
     * that the webhook was actually sent by Stripe, not spoofed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        if (! $signature || ! $webhookSecret) {
            return response()->json(['error' => 'Missing signature or webhook secret'], 400);
        }

        try {
            Webhook::constructEvent(
                $request->getContent(),
                $signature,
                $webhookSecret
            );
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        return $next($request);
    }
}
