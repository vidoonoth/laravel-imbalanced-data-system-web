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
        if (Schema::hasTable('detection_scans')) {
            return;
        }

        Schema::create('detection_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('original_filename');
            $table->string('stored_path')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->string('status')->default('processing')->index();
            $table->unsignedInteger('total_samples')->default(0);
            $table->unsignedInteger('normal_count')->default(0);
            $table->unsignedInteger('attack_count')->default(0);
            $table->decimal('normal_percentage', 7, 4)->default(0);
            $table->decimal('attack_percentage', 7, 4)->default(0);
            $table->json('raw_summary')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detection_scans');
    }
};
