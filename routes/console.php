<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule enrollment period status updates to run daily at midnight
Schedule::command('enrollment-periods:update-status --notify')
    ->daily()
    ->at('00:00')
    ->timezone('Asia/Manila');

// Payment reminder scheduler - runs daily at 8 AM
Schedule::command('enrollment:send-payment-reminders')
    ->dailyAt('08:00')
    ->timezone('Asia/Manila')
    ->withoutOverlapping()
    ->onOneServer();

// Audit logs cleanup - runs daily at 2 AM, keeps logs for 90 days
Schedule::command('audit-logs:clean --days=90')
    ->dailyAt('02:00')
    ->timezone('Asia/Manila')
    ->withoutOverlapping()
    ->onOneServer();
