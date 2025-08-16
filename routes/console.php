<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule image cleanup to run weekly
Schedule::command('images:cleanup --force')
    ->weekly()
    ->sundays()
    ->at('02:00')
    ->description('Clean up unused product images');
