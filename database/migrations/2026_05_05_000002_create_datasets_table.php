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
        if (! Schema::hasTable('dataset_imports')) {
            Schema::create('dataset_imports', function (Blueprint $table) {
                $table->id();
                $table->char('source_fingerprint', 64)->unique();
                $table->string('source_host')->nullable();
                $table->text('source_path');
                $table->string('source_filename')->nullable();
                $table->unsignedBigInteger('size_bytes')->nullable();
                $table->timestamp('last_modified_at')->nullable();
                $table->char('checksum_sha256', 64)->nullable();
                $table->string('status', 32)->default('processing')->index();
                $table->unsignedInteger('rows_imported')->default(0);
                $table->text('error_message')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();

                $table->index(['source_filename', 'status']);
            });
        }

        if (! Schema::hasTable('datasets')) {
            Schema::create('datasets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dataset_import_id')->constrained('dataset_imports')->cascadeOnDelete();
                $table->unsignedInteger('row_number');
                $table->char('row_hash', 64);
                $table->json('payload');
                $table->timestamps();

                $table->unique(['dataset_import_id', 'row_number']);
                $table->index('row_hash');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datasets');
        Schema::dropIfExists('dataset_imports');
    }
};
