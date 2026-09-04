<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Exception;

class PaymentService
{
    /**
     * Create a new payment attempt for an order idempotently.
     *
     * @param Order $order
     * @param string $idempotencyKey
     * @param string $provider
     * @param float $amount
     * @param string $currency
     * @return PaymentAttempt
     * @throws Exception
     */
    public function createPaymentAttempt(Order $order, string $idempotencyKey, string $provider, float $amount, string $currency = 'USD'): PaymentAttempt
    {
        return DB::transaction(function () use ($order, $idempotencyKey, $provider, $amount, $currency) {
            try {
                return PaymentAttempt::firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'idempotency_key' => $idempotencyKey,
                    ],
                    [
                        'provider' => $provider,
                        'amount' => $amount,
                        'currency' => $currency,
                        'status' => 'pending',
                    ]
                );
            } catch (QueryException $e) {
                // Check if it's a unique constraint violation (MySQL code 1062 / SQLSTATE 23000)
                if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1062')) {
                    return PaymentAttempt::where('order_id', $order->id)
                        ->where('idempotency_key', $idempotencyKey)
                        ->firstOrFail();
                }

                // If it's a different DB error (connection, FK, lock timeout), rethrow it
                throw $e;
            }
        });
    }
}
