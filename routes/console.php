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
| Generates the Excel report for the current Asia/Manila calendar day at
| 11:59 PM. Requires the server cron:
|
|   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
|
| Replace /path-to-project with this application's absolute path.
|
*/
Schedule::command('generate:daily-visitor-report')
    ->dailyAt('23:59')
    ->timezone('Asia/Manila')
    ->withoutOverlapping(30)
    ->appendOutputTo(storage_path('logs/daily-visitor-report-scheduler.log'));
