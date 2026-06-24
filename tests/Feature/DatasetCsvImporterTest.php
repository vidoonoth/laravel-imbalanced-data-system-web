<?php

use App\Models\Dataset;
use App\Models\DatasetImport;
use App\Services\Datasets\DatasetCsvImporter;

test('csv importer stores dataset rows and skips an already completed source file', function () {
    $path = tempnam(sys_get_temp_dir(), 'dataset_');

    file_put_contents($path, implode(PHP_EOL, [
        'source_ip,destination_ip,bytes',
        '10.10.10.1,10.10.10.2,120',
        '',
        '10.10.10.3,10.10.10.4,240',
    ]));

    try {
        $importer = app(DatasetCsvImporter::class);
        $source = [
            'host' => '103.245.38.142',
            'path' => '/var/www/syslog-datasets/sample.csv',
            'filename' => 'sample.csv',
        ];

        $firstResult = $importer->importFile($path, $source);

        expect($firstResult['status'])
            ->toBe('imported')
            ->and($firstResult['rows_imported'])->toBe(2)
            ->and(DatasetImport::query()->count())->toBe(1)
            ->and(Dataset::query()->count())->toBe(2)
            ->and(Dataset::query()->first()->payload['source_ip'])->toBe('10.10.10.1');

        $secondResult = $importer->importFile($path, $source);

        expect($secondResult['status'])
            ->toBe('skipped')
            ->and(DatasetImport::query()->count())->toBe(1)
            ->and(Dataset::query()->count())->toBe(2);
    } finally {
        @unlink($path);
    }
});
