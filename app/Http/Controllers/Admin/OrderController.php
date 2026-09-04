<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Admin\OrderService;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function index()
    {
        return Inertia::render('Admin/Orders/Index', [
            'orders' => $this->orderService->getOrders(),
        ]);
    }

    public function show(Order $order)
    {
        return Inertia::render('Admin/Orders/Show', [
            'order' => $this->orderService->getOrderWithPipeline($order),
        ]);
    }
}
