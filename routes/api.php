<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\CheckoutController;

Route::prefix('v1')->group(function () {
    Route::get('/courses', [CourseController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // Order status polling endpoint (for checkout success page)
        Route::get('/orders/{orderId}/status', [CheckoutController::class, 'status']);
    });
});

// Stripe webhook — uses signature verification middleware, NOT auth
Route::post('/webhooks/stripe', [WebhookController::class, 'handleStripe'])
    ->middleware(\App\Http\Middleware\VerifyStripeWebhook::class);

