<?php

namespace App\Jobs;

use App\Actions\FulfillOrderAction;
use App\Actions\ProcessRefundAction;
use App\Models\WebhookEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessWebhookEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    public function __construct(
        public int $webhookEventId
    ) {}

    public function handle(): void
    {
        $webhookEvent = WebhookEvent::findOrFail($this->webhookEventId);

        // Already processed — skip (idempotent)
        if ($webhookEvent->processing_status === 'processed') {
            return;
        }

        // Mark as processing
        $webhookEvent->update(['processing_status' => 'processing']);

        try {
            $payload = $webhookEvent->payload;

            match ($webhookEvent->event_type) {
                'payment_intent.succeeded' => $this->handlePaymentSucceeded($payload),
                'charge.refunded' => $this->handleChargeRefunded($payload),
                default => Log::info("Unhandled webhook event type: {$webhookEvent->event_type}"),
            };

            $webhookEvent->update([
                'processing_status' => 'processed',
                'processed_at' => now(),
            ]);
        } catch (Throwable $e) {
            $webhookEvent->update([
                'processing_status' => 'failed',
                'error_information' => $e->getMessage(),
            ]);

            throw $e; // Re-throw so the queue retries
        }
    }

    protected function handlePaymentSucceeded(array $payload): void
    {
        $paymentIntent = $payload['data']['object'] ?? [];
        $providerPaymentId = $paymentIntent['id'] ?? null;

        // The charge ID is the provider_transaction_id
        $charges = $paymentIntent['latest_charge'] ?? null;
        $providerTransactionId = is_string($charges) ? $charges : ($charges['id'] ?? $providerPaymentId);

        $amount = ($paymentIntent['amount_received'] ?? $paymentIntent['amount'] ?? 0) / 100;
        $currency = strtoupper($paymentIntent['currency'] ?? 'usd');

        app(FulfillOrderAction::class)->execute(
            $providerPaymentId,
            $providerTransactionId,
            $amount,
            $currency,
        );
    }

    protected function handleChargeRefunded(array $payload): void
    {
        $charge = $payload['data']['object'] ?? [];
        $providerTransactionId = $charge['id'] ?? null;

        // Get the latest refund from the charge
        $refunds = $charge['refunds']['data'] ?? [];
        $latestRefund = $refunds[0] ?? null;

        if (! $latestRefund) {
            Log::warning("charge.refunded webhook received but no refund data found", $payload);
            return;
        }

        $providerRefundId = $latestRefund['id'];
        $refundAmount = ($latestRefund['amount'] ?? 0) / 100;
        $currency = strtoupper($latestRefund['currency'] ?? 'usd');

        app(ProcessRefundAction::class)->execute(
            $providerTransactionId,
            $providerRefundId,
            $refundAmount,
            $currency,
        );
    }
}
