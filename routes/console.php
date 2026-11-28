<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-checkout any employees who did not logout before 7:00 PM IST
Schedule::command('attendance:auto-checkout')->dailyAt('19:00')->timezone('Asia/Kolkata');
Schedule::command('attendance:auto-checkout')->everyFifteenMinutes()->between('19:00', '23:59')->timezone('Asia/Kolkata');

// Auto-delete attendance selfie images older than 45 days from storage & database
Schedule::command('attendance:cleanup-selfies --days=45')->dailyAt('02:00')->timezone('Asia/Kolkata');

