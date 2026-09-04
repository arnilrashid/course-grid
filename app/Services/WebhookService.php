<?php

namespace App\Services;

use App\Models\WebhookEvent;
use Illuminate\Database\QueryException;

class WebhookService
{
    /**
     * Ingest a webhook event idempotently.
     *
     * Uses firstOrCreate with explicit duplicate-key catch on the
     * unique (provider, provider_event_id) constraint. On race condition,
     * catches SQLSTATE 23000 / MySQL 1062 and fetches the existing row.
     * Rethrows any other database error.
     */
    public function ingestEvent(string $provider, string $eventId, string $eventType, array $payload): WebhookEvent
    {
        try {
            return WebhookEvent::firstOrCreate(
                [
                    'provider' => $provider,
                    'provider_event_id' => $eventId,
                ],
                [
                    'event_type' => $eventType,
                    'payload' => $payload,
                    'processing_status' => 'pending',
                    'received_at' => now(),
                ]
            );
        } catch (QueryException $e) {
            // Duplicate-key: two simultaneous deliveries raced past SELECT
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1062')) {
                return WebhookEvent::where('provider', $provider)
                    ->where('provider_event_id', $eventId)
                    ->firstOrFail();
            }

            // Not a duplicate-key error — rethrow (don't catch broadly)
            throw $e;
        }
    }
}
