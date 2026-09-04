<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Poll Stripe every 15 minutes for stale pending payments that missed their webhook
Schedule::command('payments:reconcile')->everyFifteenMinutes();

