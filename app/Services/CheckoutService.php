<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CheckoutService
{
    /**
     * Create an order and its items from a collection of courses idempotently.
     */
    public function createOrderFromCourses(User $user, Collection $courses, string $idempotencyKey): Order
    {
        // Create order idempotently
        $order = Order::firstOrCreate(
            ['user_id' => $user->id, 'idempotency_key' => $idempotencyKey],
            [
                'subtotal' => $courses->sum('price'),
                'discount_total' => 0,
                'tax_total' => 0,
                'total' => $courses->sum('price'),
                'currency' => 'USD',
                'status' => 'pending',
                'payment_method' => 'stripe',
            ]
        );

        // Create order items if this is a fresh order
        if ($order->wasRecentlyCreated) {
            foreach ($courses as $course) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'course_id' => $course->id,
                    'price' => $course->price,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => $course->price,
                    'currency' => 'USD',
                ]);
            }
        }

        return $order;
    }
}
