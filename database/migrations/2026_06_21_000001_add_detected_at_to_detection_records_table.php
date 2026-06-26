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
        if (! Schema::hasTable('detection_results') || Schema::hasColumn('detection_results', 'detected_at')) {
            return;
        }

        Schema::table('detection_results', function (Blueprint $table) {
            $table->dateTime('detected_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('detection_results') || ! Schema::hasColumn('detection_results', 'detected_at')) {
            return;
        }

        Schema::table('detection_results', function (Blueprint $table) {
            $table->dropColumn('detected_at');
        });
    }
};
