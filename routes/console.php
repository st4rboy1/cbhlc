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
