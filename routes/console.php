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

Schedule::command('detection:run-flask')
    ->when(fn () => (bool) config('services.ml.enabled', true))
    ->cron(match(config('services.ml.schedule', 'hourly')) {
        'hourly' => '0 * * * *',
        'every_30_minutes' => '*/30 * * * *',
        'every_15_minutes' => '*/15 * * * *',
        'daily_at' => '0 ' . explode(':', config('services.ml.schedule_time', '13:00'))[0] . ' * * *',
        default => '0 * * * *',
    })
    ->timezone(config('services.ml.schedule_timezone', 'Asia/Jakarta'))
    ->withoutOverlapping()
    ->runInBackground();
