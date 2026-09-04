<?php

namespace App\Console\Commands;

use App\Actions\FulfillOrderAction;
use App\Models\PaymentAttempt;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Throwable;

class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile
                            {--threshold=30 : Minutes after which a pending payment is considered stale}';

    protected $description = 'Poll Stripe for stale pending payments that may have missed their webhook';

    public function handle(): int
    {
        $thresholdMinutes = (int) $this->option('threshold');
        $stripe = new StripeClient(config('services.stripe.secret'));

        $staleAttempts = PaymentAttempt::where('provider', 'stripe')
            ->where('status', 'pending')
            ->whereNotNull('provider_payment_id')
            ->where('created_at', '<', now()->subMinutes($thresholdMinutes))
            ->get();

        if ($staleAttempts->isEmpty()) {
            $this->info('No stale pending payments found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$staleAttempts->count()} stale pending payment(s) older than {$thresholdMinutes} minutes.");

        $reconciled = 0;
        $failed = 0;

        foreach ($staleAttempts as $attempt) {
            try {
                $paymentIntent = $stripe->paymentIntents->retrieve($attempt->provider_payment_id);

                if ($paymentIntent->status === 'succeeded') {
                    $this->line("  Payment {$attempt->provider_payment_id}: succeeded on Stripe but not fulfilled locally. Fulfilling...");

                    // Get the charge ID for the transaction
                    $chargeId = $paymentIntent->latest_charge;
                    if (is_object($chargeId)) {
                        $chargeId = $chargeId->id;
                    }

                    $amount = $paymentIntent->amount_received / 100;
                    $currency = strtoupper($paymentIntent->currency);

                    app(FulfillOrderAction::class)->execute(
                        $attempt->provider_payment_id,
                        $chargeId ?? $attempt->provider_payment_id,
                        $amount,
                        $currency,
                    );

                    $reconciled++;
                    $this->info("  ✓ Order #{$attempt->order_id} fulfilled via reconciliation.");

                } elseif (in_array($paymentIntent->status, ['canceled', 'requires_payment_method'])) {
                    $attempt->update([
                        'status' => 'failed',
                        'error_information' => "Stripe status: {$paymentIntent->status}",
                    ]);
                    $failed++;
                    $this->warn("  ✗ Payment {$attempt->provider_payment_id} failed on Stripe (status: {$paymentIntent->status}). Marked as failed.");

                } else {
                    // Still processing (requires_confirmation, processing, etc.) — skip for now
                    $this->line("  ⟳ Payment {$attempt->provider_payment_id} still in-flight (status: {$paymentIntent->status}). Skipping.");
                }

            } catch (Throwable $e) {
                $this->error("  ✗ Error checking payment {$attempt->provider_payment_id}: {$e->getMessage()}");
                Log::error("ReconcilePayments error for attempt #{$attempt->id}", [
                    'error' => $e->getMessage(),
                    'provider_payment_id' => $attempt->provider_payment_id,
                ]);
            }
        }

        $this->info("Reconciliation complete: {$reconciled} fulfilled, {$failed} marked failed.");

        return Command::SUCCESS;
    }
}
