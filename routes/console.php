<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Daily Visitor Report Scheduler
|--------------------------------------------------------------------------
|
| End-of-day generation at 11:59 PM Asia/Manila, plus an hourly catch-up
| so missed days are filled when the scheduler is running.
|
| Windows (local): run every minute via Task Scheduler, or keep this open:
|   php artisan schedule:work
|
| Linux / production cron:
|   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
|
*/
Schedule::command('generate:daily-visitor-report')
    ->dailyAt('23:59')
    ->timezone('Asia/Manila')
    ->withoutOverlapping(30)
    ->appendOutputTo(storage_path('logs/daily-visitor-report-scheduler.log'));

Schedule::command('generate:daily-visitor-report --catch-up=7')
    ->hourly()
    ->timezone('Asia/Manila')
    ->withoutOverlapping(30)
    ->appendOutputTo(storage_path('logs/daily-visitor-report-scheduler.log'));
