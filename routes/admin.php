<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', \Spatie\Permission\Middleware\RoleMiddleware::class . ':admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/role', [\App\Http\Controllers\Admin\UserController::class, 'updateRole'])->name('users.updateRole');
    Route::post('/users/{user}/suspend', [\App\Http\Controllers\Admin\UserController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{user}/unsuspend', [\App\Http\Controllers\Admin\UserController::class, 'unsuspend'])->name('users.unsuspend');
    Route::post('/users/{user}/revoke-sessions', [\App\Http\Controllers\Admin\UserController::class, 'revokeSessions'])->name('users.revokeSessions');
    Route::delete('/users/{user}/sessions/{sessionId}', [\App\Http\Controllers\Admin\UserController::class, 'revokeSession'])->name('users.revokeSession');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/impersonate', [\App\Http\Controllers\Admin\UserController::class, 'impersonate'])->name('users.impersonate');

    // Roles
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class)->only(['index', 'edit', 'update']);

    // Courses
    Route::get('/courses', [\App\Http\Controllers\Admin\CourseController::class, 'index'])->name('courses.index');
    Route::post('/courses/{course}/approve', [\App\Http\Controllers\Admin\CourseController::class, 'approve'])->name('courses.approve');
    Route::post('/courses/{course}/reject', [\App\Http\Controllers\Admin\CourseController::class, 'reject'])->name('courses.reject');

    // Orders & Payments
    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');

    // Payouts
    Route::get('/payouts', [\App\Http\Controllers\Admin\PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts/{payout}/mark-paid', [\App\Http\Controllers\Admin\PayoutController::class, 'markPaid'])->name('payouts.mark_paid');

    // Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
});
