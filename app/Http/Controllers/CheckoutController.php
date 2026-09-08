<?php

namespace App\Http\Controllers;

use App\Http\Requests\InitiateCheckoutRequest;
use App\Models\Course;
use App\Models\Order;
use App\Services\CheckoutService;
use App\Services\StripeCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService,
        private StripeCheckoutService $stripeCheckoutService,
    ) {
    }

    /**
     * Initiate checkout: create an order from the cart and redirect to Stripe.
     */
    public function initiate(InitiateCheckoutRequest $request)
    {
        $user = Auth::user();
        $courseIds = $request->input('course_ids');
        $courses = Course::whereIn('id', $courseIds)->get();

        $idempotencyKey = $request->input('idempotency_key', Str::uuid()->toString());

        $order = $this->checkoutService->createOrderFromCourses($user, $courses, $idempotencyKey);

        $result = $this->stripeCheckoutService->createCheckoutSession($order);

        return redirect()->away($result['session_url']);
    }

    /**
     * Handle the redirect back from Stripe after a successful payment.
     *
     * Revision #3: The success page renders immediately in a "processing" state.
     * It does NOT block or sleep waiting for webhook fulfillment. The webhook
     * (backed by ReconcilePayments as fallback) is the sole source of truth for
     * when the order is actually fulfilled. The frontend polls /api/v1/orders/{id}/status.
     */
    public function success(Request $request): Response
    {
        $orderId = $request->query('order_id');
        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return Inertia::render('checkout/success', [
            'order' => $order->load('items.course'),
            'fulfilled' => $order->status === \App\Enums\OrderStatus::COMPLETED,
        ]);
    }

    /**
     * Handle the redirect back from Stripe after a cancelled payment.
     */
    public function cancel(Request $request): Response
    {
        $orderId = $request->query('order_id');
        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->first();

        return Inertia::render('checkout/cancel', [
            'order' => $order,
        ]);
    }

    /**
     * API endpoint for frontend to poll order fulfillment status.
     *
     * Returns the current order status so the success page can update
     * from "processing" to "fulfilled" without blocking the HTTP request.
     */
    public function status(Request $request, int $orderId): JsonResponse
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'order_id' => $order->id,
            'status' => $order->status->value,
            'fulfilled' => $order->status === \App\Enums\OrderStatus::COMPLETED,
        ]);
    }
}
