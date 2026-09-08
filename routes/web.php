<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CourseController;
use App\Http\Controllers\CheckoutController;

Route::inertia('/', 'welcome')->name('home');
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/courses/{course:slug}/lessons/{lesson}/video', [CourseController::class, 'streamVideo'])->name('courses.video');
// OAuth Routes
Route::get('/auth/{provider}', [\App\Http\Controllers\Auth\SocialLoginController::class, 'redirect'])->name('auth.social.redirect');
Route::get('/auth/{provider}/callback', [\App\Http\Controllers\Auth\SocialLoginController::class, 'callback'])->name('auth.social.callback');
Route::delete('/auth/{provider}/unlink', [\App\Http\Controllers\Auth\SocialLoginController::class, 'unlink'])->name('auth.social.unlink')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', \App\Http\Controllers\DashboardRedirectController::class)->name('dashboard');
    Route::post('/stop-impersonating', [\App\Http\Controllers\Admin\UserController::class, 'stopImpersonating'])->name('impersonation.stop');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Checkout routes
    Route::post('/checkout', [CheckoutController::class, 'initiate'])->name('checkout.initiate');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/student.php';
require __DIR__.'/instructor.php';
