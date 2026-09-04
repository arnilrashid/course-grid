<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\PaymentAttempt;
use App\Models\PaymentTransaction;
use App\Services\EarningService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FulfillOrderAction
{
    public function __construct(
        protected EarningService $earningService
    ) {}

    /**
     * Fulfill an order after a successful payment.
     *
     * Every step uses catch-and-continue on duplicate-key exceptions so the entire
     * action is safely re-runnable when a webhook is redelivered after a prior run
     * has already completed some or all of the chain.
     *
     * @param string $providerPaymentId The Stripe PaymentIntent ID
     * @param string $providerTransactionId The Stripe charge/balance_transaction ID
     * @param float $amount Amount received
     * @param string $currency Currency code
     */
    public function execute(
        string $providerPaymentId,
        string $providerTransactionId,
        float $amount,
        string $currency = 'USD',
    ): void {
        // 1. Find the payment attempt by provider_payment_id
        $paymentAttempt = PaymentAttempt::where('provider', 'stripe')
            ->where('provider_payment_id', $providerPaymentId)
            ->firstOrFail();

        $order = $paymentAttempt->order;

        // 2. Create payment_transaction (idempotent via unique provider+provider_transaction_id)
        $paymentTransaction = $this->createIdempotent(
            PaymentTransaction::class,
            ['provider' => 'stripe', 'provider_transaction_id' => $providerTransactionId],
            [
                'payment_attempt_id' => $paymentAttempt->id,
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'succeeded',
            ]
        );

        // 3. Create order_fulfillment (idempotent via unique order_id)
        $this->createIdempotent(
            OrderFulfillment::class,
            ['order_id' => $order->id],
            [
                'payment_transaction_id' => $paymentTransaction->id,
                'status' => 'fulfilled',
                'fulfilled_at' => now(),
            ]
        );

        // 4. Update order status (safe to run multiple times)
        $order->update(['status' => OrderStatus::COMPLETED]);

        // 5. Update payment attempt status
        $paymentAttempt->update(['status' => 'succeeded']);

        // 6. For each order item: create enrollment + earning
        $order->load('items.course');

        foreach ($order->items as $orderItem) {
            $course = $orderItem->course;

            // Create enrollment (idempotent via unique user_id+course_id)
            $this->createIdempotent(
                Enrollment::class,
                ['user_id' => $order->user_id, 'course_id' => $course->id],
                [
                    'order_item_id' => $orderItem->id,
                    'status' => 'active',
                    'progress' => 0,
                ]
            );

            // Resolve revenue share for this course/instructor
            $instructorId = $course->user_id;
            $revenueSharePct = $this->earningService->resolveRevenueSharePercentage(
                $course->id,
                $instructorId
            );

            $itemTotal = (float) $orderItem->price;
            $payeeAmount = round($itemTotal * ($revenueSharePct / 100), 2);
            $platformAmount = round($itemTotal - $payeeAmount, 2);

            // Create earning (idempotent via unique source_key)
            $this->earningService->createEarningIdempotently([
                'source_key' => "course_purchase:{$orderItem->id}",
                'instructor_id' => $instructorId,
                'order_item_id' => $orderItem->id,
                'revenue_channel' => 'course_purchase',
                'currency' => $currency,
                'allocation_base_amount' => $itemTotal,
                'platform_amount' => $platformAmount,
                'payee_amount' => $payeeAmount,
                'revenue_share_percentage_snapshot' => $revenueSharePct,
            ]);
        }

        Log::info("Order #{$order->id} fulfilled successfully via Stripe payment {$providerPaymentId}");
    }

    /**
     * Idempotent model creation: try firstOrCreate, catch duplicate-key and fetch instead.
     *
     * This is the catch-and-continue pattern from revision #2 — on a duplicate-key
     * exception, we fetch the existing row and return it so the action continues
     * from that point rather than rolling back.
     *
     * @template T of \Illuminate\Database\Eloquent\Model
     * @param class-string<T> $modelClass
     * @param array $uniqueAttributes The attributes that form the unique constraint
     * @param array $additionalAttributes The remaining attributes to set on first create
     * @return T
     */
    protected function createIdempotent(string $modelClass, array $uniqueAttributes, array $additionalAttributes)
    {
        try {
            return $modelClass::firstOrCreate($uniqueAttributes, $additionalAttributes);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1062')) {
                return $modelClass::where($uniqueAttributes)->firstOrFail();
            }

            throw $e;
        }
    }
}
