<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class PaymentAttemptIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_attempt_creation_is_idempotent()
    {
        $user = User::create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => bcrypt('password'),
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total' => 50.00,
            'status' => 'pending',
            'idempotency_key' => 'order_idem_123',
        ]);

        $service = new PaymentService();
        $idempotencyKey = 'payment_idem_456';

        // 1. Create first attempt
        $attempt1 = $service->createPaymentAttempt($order, $idempotencyKey, 'stripe', 50.00);

        $this->assertDatabaseCount('payment_attempts', 1);

        // 2. Create second attempt with same key and order
        $attempt2 = $service->createPaymentAttempt($order, $idempotencyKey, 'stripe', 50.00);

        // Should return the exact same model instance/id
        $this->assertEquals($attempt1->id, $attempt2->id);
        $this->assertDatabaseCount('payment_attempts', 1);

        // 3. Simulate race condition: forcefully insert to trigger QueryException directly
        // We can mock this by calling it while simulating the DB throwing the 23000 error
        try {
            DB::table('payment_attempts')->insert([
                'order_id' => $order->id,
                'provider' => 'stripe',
                'amount' => 50.00,
                'idempotency_key' => $idempotencyKey, // same key
            ]);
            $this->fail('Expected unique constraint violation');
        } catch (QueryException $e) {
            $this->assertTrue($e->getCode() === '23000' || str_contains($e->getMessage(), '1062'));
        }
        
        $this->assertDatabaseCount('payment_attempts', 1);
    }
}
