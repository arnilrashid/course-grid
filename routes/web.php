<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CourseController;
use App\Http\Controllers\CheckoutController;

Route::inertia('/', 'welcome')->name('home');
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', \App\Http\Controllers\DashboardRedirectController::class)->name('dashboard');

    // Checkout routes
    Route::post('/checkout', [CheckoutController::class, 'initiate'])->name('checkout.initiate');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
    
    // Impersonation
    Route::post('/stop-impersonating', [\App\Http\Controllers\Admin\UserController::class, 'stopImpersonating'])->name('impersonation.stop');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/student.php';
require __DIR__.'/instructor.php';
