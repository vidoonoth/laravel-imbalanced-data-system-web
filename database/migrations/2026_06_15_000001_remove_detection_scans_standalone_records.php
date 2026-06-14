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
        Schema::table('detection_records', function (Blueprint $table) {
            // Drop foreign key first
            if (Schema::hasColumn('detection_records', 'detection_scan_id')) {
                $table->dropForeign(['detection_scan_id']);
                $table->dropColumn('detection_scan_id');
            }
            
            // Add detected_at column
            if (!Schema::hasColumn('detection_records', 'detected_at')) {
                $table->timestamp('detected_at')->nullable()->useCurrent()->index();
            }
        });

        // Drop detection_scans table
        Schema::dropIfExists('detection_scans');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate detection_scans table if rolled back (basic structure)
        if (!Schema::hasTable('detection_scans')) {
            Schema::create('detection_scans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('original_filename');
                $table->string('stored_path');
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('mime_type')->nullable();
                $table->string('status', 32)->default('pending');
                $table->unsignedInteger('total_samples')->nullable();
                $table->unsignedInteger('normal_count')->nullable();
                $table->unsignedInteger('attack_count')->nullable();
                $table->decimal('normal_percentage', 5, 2)->nullable();
                $table->decimal('attack_percentage', 5, 2)->nullable();
                $table->json('raw_summary')->nullable();
                $table->string('error_message')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('detection_records', function (Blueprint $table) {
            if (!Schema::hasColumn('detection_records', 'detection_scan_id')) {
                $table->foreignId('detection_scan_id')->nullable()->constrained('detection_scans')->cascadeOnDelete();
            }
            if (Schema::hasColumn('detection_records', 'detected_at')) {
                $table->dropColumn('detected_at');
            }
        });
    }
};
