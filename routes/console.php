<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// One Hostinger cron entry invokes schedule:run every minute.
// Both commands start at Manila midnight and generate yesterday only.
// Background execution prevents the admin task from delaying the office task.
Schedule::command('generate:daily-visitor-report')
    ->dailyAt('00:00')
    ->timezone('Asia/Manila')
    ->onOneServer()
    ->withoutOverlapping(60)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/daily-visitor-report-scheduler.log'));

Schedule::command('generate:office-daily-visitor-reports')
    ->dailyAt('00:00')
    ->timezone('Asia/Manila')
    ->onOneServer()
    ->withoutOverlapping(60)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/office-daily-visitor-report-scheduler.log'));
