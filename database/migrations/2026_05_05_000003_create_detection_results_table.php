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
        if (Schema::hasTable('detection_results')) {
            return;
        }

        Schema::create('detection_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')
                ->nullable()
                ->constrained('datasets')
                ->nullOnDelete();
            $table->dateTime('detected_at')->nullable()->index();
            $table->unsignedInteger('row_index')->default(0);

            $table->dateTime('update_time')->nullable()->index();
            $table->string('sn', 64)->nullable();
            $table->string('log_type', 64)->nullable()->index();
            $table->longText('log')->nullable();
            $table->string('event_name', 128)->nullable();
            $table->string('disposition', 32)->nullable()->index();
            $table->unsignedTinyInteger('priority')->nullable();
            $table->string('protocol', 64)->nullable();
            $table->string('source_ip', 64)->nullable();
            $table->string('destination_ip', 64)->nullable();
            $table->unsignedInteger('source_port')->nullable();
            $table->unsignedInteger('destination_port')->nullable();
            $table->string('source_interface', 64)->nullable();
            $table->string('destination_interface', 64)->nullable();
            $table->string('policy')->nullable();
            $table->unsignedInteger('pckt_len')->nullable();
            $table->unsignedSmallInteger('ttl')->nullable();
            $table->unsignedBigInteger('sent_bytes')->nullable();
            $table->unsignedBigInteger('rcvd_bytes')->nullable();
            $table->string('geo_src', 16)->nullable();
            $table->string('geo_dst', 16)->nullable();
            $table->string('action')->nullable();

            $table->unsignedTinyInteger('prediction')->nullable();
            $table->string('prediction_label', 32)->nullable();
            $table->decimal('confidence', 8, 6)->nullable();
            $table->decimal('probability_normal', 8, 6)->nullable();
            $table->decimal('probability_attack', 8, 6)->nullable();
            $table->json('raw_record')->nullable();
            $table->timestamps();

            $table->unique('dataset_id');
            $table->index(['prediction', 'source_ip']);
            $table->index(['source_ip', 'destination_ip']);
            $table->index(['disposition', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detection_results');
    }
};
