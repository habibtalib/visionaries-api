<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('push:daily-morning')->dailyAt('08:00')->timezone('Asia/Kuala_Lumpur');
Schedule::command('push:evening-checkin')->dailyAt('21:00')->timezone('Asia/Kuala_Lumpur');
Schedule::command('push:weekly-review')->weeklyOn(5, '09:00')->timezone('Asia/Kuala_Lumpur');
