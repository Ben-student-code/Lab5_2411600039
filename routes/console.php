<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ==================== LAB 6: DAILY WORKOUT REMINDER ====================
// Runs every day at 20:00 (8 PM)
Schedule::command('workouts:send-reminders')->dailyAt('20:00');