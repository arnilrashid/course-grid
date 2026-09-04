<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('instructor')->name('instructor.')->group(function () {
    Route::get('/dashboard', function () {
        // Mock Instructor Dashboard for now
        return \Inertia\Inertia::render('Instructor/Dashboard', [
            'courses' => []
        ]);
    })->name('dashboard');
});
