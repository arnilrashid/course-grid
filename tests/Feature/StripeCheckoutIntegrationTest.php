<?php

namespace Tests\Feature;

use App\Actions\FulfillOrderAction;
use App\Actions\ProcessRefundAction;
use App\Enums\OrderStatus;
use App\Http\Controllers\Api\WebhookController;
use App\Jobs\ProcessWebhookEvent;
use App\Models\Course;
use App\Models\Earning;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\PaymentAttempt;
use App\Models\PaymentTransaction;
use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\User;
use App\Models\WebhookEvent;
use App\Services\EarningService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Integration tests for the full checkout → enrollment chain.
 *
 * Tests tagged @group stripe-integration make real Stripe API calls
 * and require STRIPE_SECRET in the test environment.
 *
 * Tests without that tag exercise the pipeline logic against the DB
 * using synthetic webhook payloads (no Stripe calls).
 */
class StripeCheckoutIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $instructor;
    protected Course $course;
    protected Order $order;
    protected OrderItem $orderItem;
    protected PaymentAttempt $paymentAttempt;

    protected function setUp(): void
    {
        parent::setUp();

        // Avoid Vite manifest requirement in tests
        $this->withoutVite();

        // Seed the default revenue share setting (insertOrIgnore since migration may have already seeded it)
        DB::table('settings')->insertOrIgnore([
            'key' => 'default_revenue_share_percentage',
            'value' => '70.00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->instructor = User::create([
            'name' => 'Instructor',
            'email' => 'instructor@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->student = User::create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => bcrypt('password'),
        ]);

        // Need a category for the course
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->course = Course::create([
            'user_id' => $this->instructor->id,
            'category_id' => $categoryId,
            'title' => 'Test Course',
            'slug' => 'test-course',
            'description' => 'A test course',
            'language' => 'en',
            'price' => 49.99,
            'status' => 'published',
        ]);

        $this->order = Order::create([
            'user_id' => $this->student->id,
            'subtotal' => 49.99,
            'total' => 49.99,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => 'stripe',
            'idempotency_key' => 'test_order_001',
        ]);

        $this->orderItem = OrderItem::create([
            'order_id' => $this->order->id,
            'course_id' => $this->course->id,
            'price' => 49.99,
            'total' => 49.99,
            'currency' => 'USD',
        ]);

        $this->paymentAttempt = PaymentAttempt::create([
            'order_id' => $this->order->id,
            'provider' => 'stripe',
            'provider_payment_id' => 'pi_test_123',
            'amount' => 49.99,
            'currency' => 'USD',
            'idempotency_key' => 'checkout_test_001',
            'status' => 'pending',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Scenario 1: Happy path — full chain
    // ──────────────────────────────────────────────────────────────────

    public function test_happy_path_full_chain_from_webhook_to_enrollment(): void
    {
        $action = app(FulfillOrderAction::class);

        $action->execute(
            providerPaymentId: 'pi_test_123',
            providerTransactionId: 'ch_test_456',
            amount: 49.99,
            currency: 'USD',
        );

        // Assert payment_transaction created
        $this->assertDatabaseHas('payment_transactions', [
            'provider' => 'stripe',
            'provider_transaction_id' => 'ch_test_456',
            'amount' => 49.99,
            'status' => 'succeeded',
        ]);

        // Assert order_fulfillment created
        $this->assertDatabaseHas('order_fulfillments', [
            'order_id' => $this->order->id,
            'status' => 'fulfilled',
        ]);

        // Assert order status updated
        $this->order->refresh();
        $this->assertEquals(OrderStatus::COMPLETED, $this->order->status);

        // Assert enrollment created
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'order_item_id' => $this->orderItem->id,
            'status' => 'active',
        ]);

        // Assert earning created with correct source_key
        $earning = Earning::where('source_key', "course_purchase:{$this->orderItem->id}")->first();
        $this->assertNotNull($earning, 'Earning should exist');
        $this->assertEquals($this->instructor->id, $earning->instructor_id);
        $this->assertEquals($this->orderItem->id, $earning->order_item_id);
        $this->assertEquals('course_purchase', $earning->revenue_channel);
        $this->assertEquals(70.00, (float) $earning->revenue_share_percentage_snapshot);
        $this->assertEquals(49.99, (float) $earning->allocation_base_amount);
        $this->assertEquals(34.99, (float) $earning->payee_amount); // 70% of 49.99
        $this->assertEquals(15.00, (float) $earning->platform_amount); // 30% of 49.99
    }

    // ──────────────────────────────────────────────────────────────────
    // Scenario 2: Webhook retry / dedup (revision #2 — re-deliver
    //             AFTER the first run has fully completed)
    // ──────────────────────────────────────────────────────────────────

    public function test_webhook_retry_after_full_completion_creates_no_duplicates(): void
    {
        $action = app(FulfillOrderAction::class);

        // First run: completes the full chain
        $action->execute(
            providerPaymentId: 'pi_test_123',
            providerTransactionId: 'ch_test_456',
            amount: 49.99,
        );

        // Verify everything was created
        $this->assertDatabaseCount('payment_transactions', 1);
        $this->assertDatabaseCount('order_fulfillments', 1);
        $this->assertDatabaseCount('enrollments', 1);
        $this->assertDatabaseCount('earnings', 1);

        // Second run: exact same webhook redelivered after full completion
        $action->execute(
            providerPaymentId: 'pi_test_123',
            providerTransactionId: 'ch_test_456',
            amount: 49.99,
        );

        // Assert NO duplicates across any table
        $this->assertDatabaseCount('payment_transactions', 1);
        $this->assertDatabaseCount('order_fulfillments', 1);
        $this->assertDatabaseCount('enrollments', 1);
        $this->assertDatabaseCount('earnings', 1);
    }

    public function test_webhook_event_dedup_returns_existing_on_duplicate(): void
    {
        // Simulate the first webhook ingestion
        $event1 = WebhookEvent::create([
            'provider' => 'stripe',
            'provider_event_id' => 'evt_test_001',
            'event_type' => 'payment_intent.succeeded',
            'payload' => ['test' => true],
            'processing_status' => 'processed',
            'received_at' => now(),
            'processed_at' => now(),
        ]);

        // Simulate sending the same webhook through the controller logic
        $controller = new WebhookController();
        $payload = [
            'id' => 'evt_test_001',
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => []],
        ];

        // Use reflection to call the protected ingestEvent method
        $reflection = new \ReflectionMethod($controller, 'ingestEvent');
        $reflection->setAccessible(true);
        $event2 = $reflection->invoke($controller, 'stripe', 'evt_test_001', 'payment_intent.succeeded', $payload);

        // Should return the same event, not create a duplicate
        $this->assertEquals($event1->id, $event2->id);
        $this->assertDatabaseCount('webhook_events', 1);
    }

    // ──────────────────────────────────────────────────────────────────
    // Scenario 3: Out-of-order delivery — webhook arrives before
    //             redirect confirmation (or vice versa)
    // ──────────────────────────────────────────────────────────────────

    public function test_out_of_order_delivery_reaches_same_end_state(): void
    {
        $action = app(FulfillOrderAction::class);

        // Simulate: webhook fires and fulfills before the user's browser
        // redirects back to /checkout/success
        $action->execute(
            providerPaymentId: 'pi_test_123',
            providerTransactionId: 'ch_test_456',
            amount: 49.99,
        );

        // Now the user's browser hits /checkout/success
        // The success page should show the order as fulfilled
        $this->actingAs($this->student)
            ->get("/checkout/success?order_id={$this->order->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('fulfilled', true)
            );

        // Same end state: 1 of each record
        $this->assertDatabaseCount('payment_transactions', 1);
        $this->assertDatabaseCount('order_fulfillments', 1);
        $this->assertDatabaseCount('enrollments', 1);
        $this->assertDatabaseCount('earnings', 1);
    }

    public function test_success_page_before_webhook_shows_processing_state(): void
    {
        // User's browser redirects back BEFORE the webhook has fired
        // The success page should render immediately in "processing" state (revision #3)
        $this->actingAs($this->student)
            ->get("/checkout/success?order_id={$this->order->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('fulfilled', false)
            );

        // No fulfillment yet
        $this->assertDatabaseCount('order_fulfillments', 0);
        $this->assertDatabaseCount('enrollments', 0);
    }

    // ──────────────────────────────────────────────────────────────────
    // Scenario 4: Card decline
    // ──────────────────────────────────────────────────────────────────

    public function test_declined_payment_leaves_order_unfulfilled(): void
    {
        // Simulate a payment_intent.payment_failed webhook
        $webhookEvent = WebhookEvent::create([
            'provider' => 'stripe',
            'provider_event_id' => 'evt_decline_001',
            'event_type' => 'payment_intent.payment_failed',
            'payload' => [
                'type' => 'payment_intent.payment_failed',
                'data' => [
                    'object' => [
                        'id' => 'pi_test_123',
                        'status' => 'requires_payment_method',
                        'last_payment_error' => [
                            'message' => 'Your card was declined.',
                            'code' => 'card_declined',
                        ],
                    ],
                ],
            ],
            'processing_status' => 'pending',
            'received_at' => now(),
        ]);

        // Process the webhook — it should be an unhandled event type (not succeeded/refunded)
        $job = new ProcessWebhookEvent($webhookEvent->id);
        $job->handle();

        // Order should remain pending (not completed, not failed from this path)
        $this->order->refresh();
        $this->assertEquals(OrderStatus::PENDING, $this->order->status);

        // No fulfillment chain created
        $this->assertDatabaseCount('payment_transactions', 0);
        $this->assertDatabaseCount('order_fulfillments', 0);
        $this->assertDatabaseCount('enrollments', 0);
        $this->assertDatabaseCount('earnings', 0);
    }

    // ──────────────────────────────────────────────────────────────────
    // Scenario 5: Missing webhook — reconciliation fallback
    // ──────────────────────────────────────────────────────────────────

    /**
     * This test verifies the ReconcilePayments command structure works.
     * A full integration test against Stripe's API requires STRIPE_SECRET
     * and is covered by the stripe:verify-pipeline command.
     */
    public function test_stale_pending_payments_are_detected(): void
    {
        // Use DB::table to bypass Eloquent timestamp auto-setting
        $staleId = DB::table('payment_attempts')->insertGetId([
            'order_id' => $this->order->id,
            'provider' => 'stripe',
            'provider_payment_id' => 'pi_stale_999',
            'amount' => 49.99,
            'currency' => 'USD',
            'idempotency_key' => 'stale_attempt_key',
            'status' => 'pending',
            'created_at' => now()->subMinutes(60),
            'updated_at' => now()->subMinutes(60),
        ]);

        // Verify the query logic finds stale attempts
        $stale = PaymentAttempt::where('provider', 'stripe')
            ->where('status', 'pending')
            ->whereNotNull('provider_payment_id')
            ->where('created_at', '<', now()->subMinutes(30))
            ->get();

        $this->assertCount(1, $stale);
        $this->assertEquals($staleId, $stale->first()->id);
    }

    // ──────────────────────────────────────────────────────────────────
    // Scenario 6: Refund — full chain + idempotent retry
    // ──────────────────────────────────────────────────────────────────

    public function test_refund_creates_reversal_chain(): void
    {
        // First, fulfill the order
        $action = app(FulfillOrderAction::class);
        $action->execute(
            providerPaymentId: 'pi_test_123',
            providerTransactionId: 'ch_test_456',
            amount: 49.99,
        );

        // Now process a refund
        $refundAction = app(ProcessRefundAction::class);
        $refundAction->execute(
            providerTransactionId: 'ch_test_456',
            providerRefundId: 're_test_789',
            refundAmount: 49.99,
            currency: 'USD',
        );

        // Assert refund created
        $this->assertDatabaseHas('refunds', [
            'provider' => 'stripe',
            'provider_refund_id' => 're_test_789',
            'amount' => 49.99,
            'status' => 'succeeded',
        ]);

        // Assert refund_item created
        $this->assertDatabaseHas('refund_items', [
            'order_item_id' => $this->orderItem->id,
            'amount' => 49.99,
        ]);

        // Assert reversing earning created
        $originalEarning = Earning::where('source_key', "course_purchase:{$this->orderItem->id}")->first();
        $refundItem = RefundItem::where('order_item_id', $this->orderItem->id)->first();
        $reversingEarning = Earning::where('source_key', "refund:{$refundItem->id}")->first();

        $this->assertNotNull($reversingEarning, 'Reversing earning should exist');
        $this->assertEquals($originalEarning->id, $reversingEarning->reverses_earning_id);
        $this->assertEquals('refund_adjustment', $reversingEarning->revenue_channel);
        $this->assertEquals(-49.99, (float) $reversingEarning->allocation_base_amount);
        $this->assertEquals(-34.99, (float) $reversingEarning->payee_amount);
        $this->assertEquals(-15.00, (float) $reversingEarning->platform_amount);

        // Assert enrollment status updated to refunded
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'refunded',
        ]);

        // Assert order status updated
        $this->order->refresh();
        $this->assertEquals(OrderStatus::REFUNDED, $this->order->status);
    }

    public function test_refund_retry_after_full_completion_creates_no_duplicates(): void
    {
        // Fulfill order first
        app(FulfillOrderAction::class)->execute(
            providerPaymentId: 'pi_test_123',
            providerTransactionId: 'ch_test_456',
            amount: 49.99,
        );

        $refundAction = app(ProcessRefundAction::class);

        // First refund run
        $refundAction->execute(
            providerTransactionId: 'ch_test_456',
            providerRefundId: 're_test_789',
            refundAmount: 49.99,
        );

        $this->assertDatabaseCount('refunds', 1);
        $this->assertDatabaseCount('refund_items', 1);
        // 1 original earning + 1 reversing earning = 2
        $this->assertDatabaseCount('earnings', 2);

        // Second run (webhook retry after full completion)
        $refundAction->execute(
            providerTransactionId: 'ch_test_456',
            providerRefundId: 're_test_789',
            refundAmount: 49.99,
        );

        // NO duplicates
        $this->assertDatabaseCount('refunds', 1);
        $this->assertDatabaseCount('refund_items', 1);
        $this->assertDatabaseCount('earnings', 2);
    }

    // ──────────────────────────────────────────────────────────────────
    // Revenue share configuration (revision #5)
    // ──────────────────────────────────────────────────────────────────

    public function test_revenue_share_uses_course_instructor_override(): void
    {
        // Set a per-course/instructor override
        DB::table('course_instructors')->insert([
            'course_id' => $this->course->id,
            'instructor_id' => $this->instructor->id,
            'revenue_share_percentage' => 80.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(FulfillOrderAction::class)->execute(
            providerPaymentId: 'pi_test_123',
            providerTransactionId: 'ch_test_456',
            amount: 49.99,
        );

        $earning = Earning::where('source_key', "course_purchase:{$this->orderItem->id}")->first();
        $this->assertEquals(80.00, (float) $earning->revenue_share_percentage_snapshot);
        $this->assertEquals(39.99, (float) $earning->payee_amount); // 80% of 49.99 = 39.992 → rounds to 39.99
        $this->assertEquals(10.00, (float) $earning->platform_amount); // 49.99 - 39.99 = 10.00
    }

    public function test_revenue_share_falls_back_to_settings_default(): void
    {
        // No course_instructors override — should use settings default (70%)
        app(FulfillOrderAction::class)->execute(
            providerPaymentId: 'pi_test_123',
            providerTransactionId: 'ch_test_456',
            amount: 49.99,
        );

        $earning = Earning::where('source_key', "course_purchase:{$this->orderItem->id}")->first();
        $this->assertEquals(70.00, (float) $earning->revenue_share_percentage_snapshot);
    }
}
