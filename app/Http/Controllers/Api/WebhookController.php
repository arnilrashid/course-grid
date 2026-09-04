<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebhookEvent;
use App\Models\WebhookEvent;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * Handle incoming Stripe webhook events.
     *
     * Dedup uses explicit duplicate-key catching (revision #1): two near-simultaneous
     * deliveries can both pass the SELECT in firstOrCreate before either INSERTs, so
     * the second will hit the unique constraint. We catch that specifically and treat
     * it as "event already received" rather than letting it bubble up.
     */
    public function handleStripe(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $eventId = $payload['id'] ?? null;
        $eventType = $payload['type'] ?? null;

        if (! $eventId || ! $eventType) {
            return response()->json(['error' => 'Invalid event payload'], 400);
        }

        // Idempotent insert with explicit duplicate-key handling
        $webhookService = new \App\Services\WebhookService();
        $webhookEvent = $webhookService->ingestEvent('stripe', $eventId, $eventType, $payload);

        // If already processed, return 200 immediately — don't re-dispatch
        if ($webhookEvent->processing_status === 'processed') {
            return response()->json(['status' => 'already_processed']);
        }

        // If already being processed (in-flight), return 200 — Stripe will stop retrying
        if ($webhookEvent->processing_status === 'processing') {
            return response()->json(['status' => 'processing']);
        }

        // Dispatch for async processing
        ProcessWebhookEvent::dispatch($webhookEvent->id);

        return response()->json(['status' => 'received']);
    }
}
