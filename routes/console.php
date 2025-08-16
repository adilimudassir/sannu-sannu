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

// Schedule project status updates to run daily
Schedule::command('projects:update-statuses')
    ->daily()
    ->at('01:00')
    ->description('Update project statuses based on dates (complete expired, activate scheduled)');

// Schedule project status updates to run hourly during business hours for more responsive updates
Schedule::command('projects:update-statuses')
    ->hourly()
    ->between('08:00', '18:00')
    ->description('Hourly project status updates during business hours');
