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
