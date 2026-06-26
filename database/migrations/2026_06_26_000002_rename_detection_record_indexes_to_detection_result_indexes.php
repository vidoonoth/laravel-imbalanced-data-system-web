<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->renameIndexes([
            'detection_records_detected_at_index' => 'detection_results_detected_at_index',
            'detection_records_update_time_index' => 'detection_results_update_time_index',
            'detection_records_log_type_index' => 'detection_results_log_type_index',
            'detection_records_disposition_index' => 'detection_results_disposition_index',
            'detection_records_prediction_source_ip_index' => 'detection_results_prediction_source_ip_index',
            'detection_records_source_ip_destination_ip_index' => 'detection_results_source_ip_destination_ip_index',
            'detection_records_disposition_priority_index' => 'detection_results_disposition_priority_index',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->renameIndexes([
            'detection_results_detected_at_index' => 'detection_records_detected_at_index',
            'detection_results_update_time_index' => 'detection_records_update_time_index',
            'detection_results_log_type_index' => 'detection_records_log_type_index',
            'detection_results_disposition_index' => 'detection_records_disposition_index',
            'detection_results_prediction_source_ip_index' => 'detection_records_prediction_source_ip_index',
            'detection_results_source_ip_destination_ip_index' => 'detection_records_source_ip_destination_ip_index',
            'detection_results_disposition_priority_index' => 'detection_records_disposition_priority_index',
        ]);
    }

    /**
     * @param  array<string, string>  $indexes
     */
    private function renameIndexes(array $indexes): void
    {
        if (! Schema::hasTable('detection_results')) {
            return;
        }

        foreach ($indexes as $from => $to) {
            if (! Schema::hasIndex('detection_results', $from) || Schema::hasIndex('detection_results', $to)) {
                continue;
            }

            Schema::table('detection_results', function (Blueprint $table) use ($from, $to) {
                $table->renameIndex($from, $to);
            });
        }
    }
};
