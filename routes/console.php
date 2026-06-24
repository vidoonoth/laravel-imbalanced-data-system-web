<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('datasets:import-vps')
    ->when(fn () => (bool) config('services.vps_csv.enabled', false))
    ->dailyAt(config('services.vps_csv.schedule_time', '12:55'))
    ->timezone(config('services.vps_csv.schedule_timezone', 'Asia/Jakarta'))
    ->withoutOverlapping();
