<?php

namespace App\Services\Admin;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderService
{
    /**
     * Get paginated orders.
     */
    public function getOrders(): LengthAwarePaginator
    {
        return Order::with(['user', 'items.course'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    /**
     * Get a single order with its full payment pipeline.
     */
    public function getOrderWithPipeline(Order $order): Order
    {
        $order->load([
            'user',
            'items.course',
            'paymentAttempts.paymentTransaction.refunds.items',
        ]);
        
        return $order;
    }
}
