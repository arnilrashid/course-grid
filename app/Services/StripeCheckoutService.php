<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentAttempt;
use Illuminate\Support\Str;
use Stripe\StripeClient;

class StripeCheckoutService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe Checkout Session for an order.
     *
     * Idempotently creates a PaymentAttempt, then creates a Stripe Checkout Session
     * and stores the provider_payment_id back on the attempt.
     *
     * @return array{session_url: string, payment_attempt: PaymentAttempt}
     */
    public function createCheckoutSession(Order $order): array
    {
        $order->load('items.course');

        $paymentService = new PaymentService();
        $idempotencyKey = 'checkout_' . $order->id . '_' . ($order->idempotency_key ?? Str::uuid());

        $paymentAttempt = $paymentService->createPaymentAttempt(
            $order,
            $idempotencyKey,
            'stripe',
            (float) $order->total,
            $order->currency ?? 'USD'
        );

        // If this attempt already has a Stripe session, return the existing URL
        if ($paymentAttempt->provider_payment_id) {
            $session = $this->stripe->checkout->sessions->retrieve($paymentAttempt->provider_payment_id);

            return [
                'session_url' => $session->url,
                'payment_attempt' => $paymentAttempt,
            ];
        }

        // Build line items from order items
        $lineItems = $order->items->map(function ($item) {
            return [
                'price_data' => [
                    'currency' => strtolower($item->currency ?? 'usd'),
                    'product_data' => [
                        'name' => $item->course->title ?? "Course #{$item->course_id}",
                    ],
                    'unit_amount' => (int) round($item->price * 100), // Stripe uses cents
                ],
                'quantity' => 1,
            ];
        })->toArray();

        $session = $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => url("/checkout/success?order_id={$order->id}"),
            'cancel_url' => url("/checkout/cancel?order_id={$order->id}"),
            'metadata' => [
                'order_id' => $order->id,
                'payment_attempt_id' => $paymentAttempt->id,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'order_id' => $order->id,
                    'payment_attempt_id' => $paymentAttempt->id,
                ],
            ],
        ], [
            'idempotency_key' => $idempotencyKey,
        ]);

        // Store the Stripe session/payment_intent ID on our payment attempt
        $paymentAttempt->update([
            'provider_payment_id' => $session->payment_intent,
        ]);

        return [
            'session_url' => $session->url,
            'payment_attempt' => $paymentAttempt,
        ];
    }
}
