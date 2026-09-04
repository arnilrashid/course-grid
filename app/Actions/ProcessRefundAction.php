<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\Earning;
use App\Models\Enrollment;
use App\Models\PaymentTransaction;
use App\Models\Refund;
use App\Models\RefundItem;
use App\Services\EarningService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ProcessRefundAction
{
    public function __construct(
        protected EarningService $earningService
    ) {}

    /**
     * Process a refund from Stripe's charge.refunded webhook.
     *
     * Every step uses catch-and-continue on duplicate-key exceptions so the entire
     * action is safely re-runnable when a webhook is redelivered.
     *
     * @param string $providerTransactionId The Stripe charge ID (links to payment_transaction)
     * @param string $providerRefundId The Stripe refund ID (re_xxx)
     * @param float $refundAmount Total refund amount
     * @param string $currency Currency code
     */
    public function execute(
        string $providerTransactionId,
        string $providerRefundId,
        float $refundAmount,
        string $currency = 'USD',
    ): void {
        // 1. Find the payment transaction by provider_transaction_id
        $paymentTransaction = PaymentTransaction::where('provider', 'stripe')
            ->where('provider_transaction_id', $providerTransactionId)
            ->firstOrFail();

        // 2. Create refund row (idempotent via unique provider+provider_refund_id)
        $refund = $this->createIdempotent(
            Refund::class,
            ['provider' => 'stripe', 'provider_refund_id' => $providerRefundId],
            [
                'payment_transaction_id' => $paymentTransaction->id,
                'amount' => $refundAmount,
                'currency' => $currency,
                'status' => 'succeeded',
            ]
        );

        // 3. Find the order via the payment chain
        $order = $paymentTransaction->paymentAttempt->order;
        $order->load('items');

        // Determine if this is a full or partial refund
        $isFullRefund = bccomp((string) $refundAmount, (string) $order->total, 2) >= 0;

        // 4. For each order item, create refund_item + reversing earning + update enrollment
        foreach ($order->items as $orderItem) {
            // For a full refund, refund the full item price; for partial, prorate
            $itemRefundAmount = $isFullRefund
                ? (float) $orderItem->price
                : round(($orderItem->price / $order->total) * $refundAmount, 2);

            // Create refund_item (idempotent — catch duplicate on refund_id+order_item_id)
            $refundItem = $this->createIdempotent(
                RefundItem::class,
                ['refund_id' => $refund->id, 'order_item_id' => $orderItem->id],
                [
                    'amount' => $itemRefundAmount,
                    'currency' => $currency,
                ]
            );

            // 5. Find the original earning to reverse
            $originalEarning = Earning::where('source_key', "course_purchase:{$orderItem->id}")->first();

            if ($originalEarning) {
                // Resolve revenue share (same percentage as original, frozen from snapshot)
                $revenueSharePct = $originalEarning->revenue_share_percentage_snapshot;
                $payeeReversal = round($itemRefundAmount * ($revenueSharePct / 100), 2);
                $platformReversal = round($itemRefundAmount - $payeeReversal, 2);

                // Create reversing earning (idempotent via source_key)
                $this->earningService->createEarningIdempotently([
                    'source_key' => "refund:{$refundItem->id}",
                    'instructor_id' => $originalEarning->instructor_id,
                    'refund_item_id' => $refundItem->id,
                    'reverses_earning_id' => $originalEarning->id,
                    'revenue_channel' => 'refund_adjustment',
                    'currency' => $currency,
                    'allocation_base_amount' => -$itemRefundAmount,
                    'platform_amount' => -$platformReversal,
                    'payee_amount' => -$payeeReversal,
                    'revenue_share_percentage_snapshot' => $revenueSharePct,
                ]);
            }

            // 6. Update enrollment status to refunded (if full refund)
            if ($isFullRefund) {
                Enrollment::where('user_id', $order->user_id)
                    ->where('course_id', $orderItem->course_id)
                    ->update(['status' => 'refunded']);
            }
        }

        // 7. Update order status
        $order->update([
            'status' => $isFullRefund ? OrderStatus::REFUNDED : OrderStatus::PARTIALLY_REFUNDED,
        ]);

        Log::info("Refund {$providerRefundId} processed for order #{$order->id} (full={$isFullRefund})");
    }

    /**
     * Idempotent model creation with catch-and-continue on duplicate-key.
     *
     * @template T of \Illuminate\Database\Eloquent\Model
     * @param class-string<T> $modelClass
     * @param array $uniqueAttributes
     * @param array $additionalAttributes
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
