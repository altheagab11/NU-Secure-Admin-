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
| Generate the COMPLETE previous day's report at 12:01 AM Asia/Manila,
| plus an hourly catch-up so missed days are filled when the scheduler
| is running. Hostinger cron should call `php artisan schedule:run`
| every minute; do not add a second cron mechanism here.
|
| Windows (local): run every minute via Task Scheduler, or keep this open:
|   php artisan schedule:work
|
| Linux / production cron:
|   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
|
*/
Schedule::command('generate:daily-visitor-report')
    ->dailyAt('00:01')
    ->timezone('Asia/Manila')
    ->withoutOverlapping(30)
    ->appendOutputTo(storage_path('logs/daily-visitor-report-scheduler.log'));

Schedule::command('generate:daily-visitor-report --catch-up=7')
    ->hourly()
    ->timezone('Asia/Manila')
    ->withoutOverlapping(30)
    ->appendOutputTo(storage_path('logs/daily-visitor-report-scheduler.log'));
