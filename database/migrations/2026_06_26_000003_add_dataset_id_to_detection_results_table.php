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
        if (! Schema::hasTable('detection_results') || ! Schema::hasTable('datasets')) {
            return;
        }

        Schema::table('detection_results', function (Blueprint $table) {
            if (! Schema::hasColumn('detection_results', 'dataset_id')) {
                $table->foreignId('dataset_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('datasets')
                    ->nullOnDelete();

                $table->unique('dataset_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('detection_results') || ! Schema::hasColumn('detection_results', 'dataset_id')) {
            return;
        }

        Schema::table('detection_results', function (Blueprint $table) {
            $table->dropUnique(['dataset_id']);
            $table->dropConstrainedForeignId('dataset_id');
        });
    }
};
