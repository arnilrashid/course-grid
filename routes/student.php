<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('my-learning')->name('student.')->group(function () {
    Route::get('/', function () {
        // Mock Student Dashboard for now
        return \Inertia\Inertia::render('Student/Dashboard', [
            'enrolledCourses' => []
        ]);
    })->name('dashboard');
});
