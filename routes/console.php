<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run at-risk detection daily at 2 AM
Schedule::command('clients:detect-at-risk')
    ->daily()
    ->at('02:00')
    ->withoutOverlapping()
    ->runInBackground();

// Archive audit logs older than 90 days, daily at 3 AM
Schedule::command('audit:archive')
    ->daily()
    ->at('03:00')
    ->withoutOverlapping()
    ->runInBackground();

// Send class reminders (24 hours before class) - run hourly
Schedule::command('automation:class-reminders')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Send win-back emails to inactive members - daily at 9 AM
Schedule::command('automation:winback')
    ->daily()
    ->at('09:00')
    ->withoutOverlapping()
    ->runInBackground();
