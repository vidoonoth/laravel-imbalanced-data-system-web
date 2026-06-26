<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('detection_results') || ! Schema::hasTable('detection_records')) {
            return;
        }

        Schema::rename('detection_records', 'detection_results');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('detection_records') || ! Schema::hasTable('detection_results')) {
            return;
        }

        Schema::rename('detection_results', 'detection_records');
    }
};
