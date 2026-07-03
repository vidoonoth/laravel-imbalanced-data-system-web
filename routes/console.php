<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$cronExpression = static function (?string $schedule, ?string $time = null): string {
    $schedule = trim((string) $schedule);

    if (preg_match('/^(\S+\s+){4}\S+$/', $schedule)) {
        return $schedule;
    }

    [$hour, $minute] = array_pad(explode(':', (string) ($time ?: '00:00')), 2, 0);
    $hour = min(max((int) $hour, 0), 23);
    $minute = min(max((int) $minute, 0), 59);

    return match ($schedule) {
        'every_minute' => '* * * * *',
        'every_5_minutes', 'every_five_minutes' => '*/5 * * * *',
        'every_15_minutes', 'every_fifteen_minutes' => '*/15 * * * *',
        'every_30_minutes', 'every_thirty_minutes' => '*/30 * * * *',
        'hourly' => '0 * * * *',
        'every_2_hours', 'every_two_hours' => '0 */2 * * *',
        'every_4_hours', 'every_four_hours' => '0 */4 * * *',
        'every_6_hours', 'every_six_hours' => '0 */6 * * *',
        'every_12_hours', 'every_twelve_hours' => '0 */12 * * *',
        'daily_at' => "{$minute} {$hour} * * *",
        default => '0 * * * *',
    };
};

$appendOption = static function (string $command, string $option, mixed $value): string {
    if ($value === null || $value === '') {
        return $command;
    }

    return "{$command} --{$option}={$value}";
};

if ((bool) config('services.malware_pipeline.enabled', false)) {
    $pipelineCommand = 'malware:run-pipeline';
    $pipelineCommand = $appendOption(
        $pipelineCommand,
        'import-limit',
        (int) config('services.malware_pipeline.import_limit', 10)
    );
    $pipelineCommand = $appendOption(
        $pipelineCommand,
        'batch-size',
        (int) config('services.malware_pipeline.batch_size', 100)
    );
    $pipelineCommand = $appendOption(
        $pipelineCommand,
        'detect-limit',
        config('services.malware_pipeline.detection_limit')
    );

    Schedule::command($pipelineCommand)
        ->cron($cronExpression(
            config('services.malware_pipeline.schedule', 'every_four_hours'),
            config('services.malware_pipeline.schedule_time', '00:00')
        ))
        ->timezone(config('services.malware_pipeline.schedule_timezone', 'Asia/Jakarta'))
        ->withoutOverlapping((int) config('services.malware_pipeline.without_overlapping_minutes', 240));
} else {
    Schedule::command('datasets:import-vps')
        ->when(fn () => (bool) config('services.vps_csv.enabled', false))
        ->cron($cronExpression(
            config('services.vps_csv.schedule', 'every_four_hours'),
            config('services.vps_csv.schedule_time', '12:55')
        ))
        ->timezone(config('services.vps_csv.schedule_timezone', 'Asia/Jakarta'))
        ->withoutOverlapping();

    Schedule::command('detection:run-flask')
        ->when(fn () => (bool) config('services.ml.enabled', true))
        ->cron($cronExpression(
            config('services.ml.schedule', 'every_four_hours'),
            config('services.ml.schedule_time', '13:00')
        ))
        ->timezone(config('services.ml.schedule_timezone', 'Asia/Jakarta'))
        ->withoutOverlapping();
}
