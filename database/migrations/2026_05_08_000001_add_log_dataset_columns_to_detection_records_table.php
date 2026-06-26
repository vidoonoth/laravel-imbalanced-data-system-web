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
        if (! Schema::hasTable('detection_results')) {
            return;
        }

        Schema::table('detection_results', function (Blueprint $table) {
            if (! Schema::hasColumn('detection_results', 'update_time')) {
                $table->dateTime('update_time')->nullable()->index();
            }

            if (! Schema::hasColumn('detection_results', 'sn')) {
                $table->string('sn', 64)->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'log_type')) {
                $table->string('log_type', 64)->nullable()->index();
            }

            if (! Schema::hasColumn('detection_results', 'log')) {
                $table->longText('log')->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'event_name')) {
                $table->string('event_name', 128)->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'disposition')) {
                $table->string('disposition', 32)->nullable()->index();
            }

            if (! Schema::hasColumn('detection_results', 'priority')) {
                $table->unsignedTinyInteger('priority')->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'source_interface')) {
                $table->string('source_interface', 64)->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'destination_interface')) {
                $table->string('destination_interface', 64)->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'policy')) {
                $table->string('policy')->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'pckt_len')) {
                $table->unsignedInteger('pckt_len')->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'ttl')) {
                $table->unsignedSmallInteger('ttl')->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'sent_bytes')) {
                $table->unsignedBigInteger('sent_bytes')->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'rcvd_bytes')) {
                $table->unsignedBigInteger('rcvd_bytes')->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'geo_src')) {
                $table->string('geo_src', 16)->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'geo_dst')) {
                $table->string('geo_dst', 16)->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'action')) {
                $table->string('action')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('detection_results')) {
            return;
        }

        Schema::table('detection_results', function (Blueprint $table) {
            $columns = [
                'update_time',
                'sn',
                'log_type',
                'log',
                'event_name',
                'disposition',
                'priority',
                'source_interface',
                'destination_interface',
                'policy',
                'pckt_len',
                'ttl',
                'sent_bytes',
                'rcvd_bytes',
                'geo_src',
                'geo_dst',
                'action',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('detection_results', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
