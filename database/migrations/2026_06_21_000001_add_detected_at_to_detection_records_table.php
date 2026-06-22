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
        if (! Schema::hasTable('detection_records') || Schema::hasColumn('detection_records', 'detected_at')) {
            return;
        }

        Schema::table('detection_records', function (Blueprint $table) {
            $table->dateTime('detected_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('detection_records') || ! Schema::hasColumn('detection_records', 'detected_at')) {
            return;
        }

        Schema::table('detection_records', function (Blueprint $table) {
            $table->dropColumn('detected_at');
        });
    }
};
