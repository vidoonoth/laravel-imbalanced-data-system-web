<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('detection_results')) {
            return;
        }

        Schema::table('detection_results', function (Blueprint $table) {
            if (! Schema::hasColumn('detection_results', 'sn')) {
                $table->string('sn', 64)->nullable()->index();
            }

            if (! Schema::hasColumn('detection_results', 'source_interface')) {
                $table->string('source_interface', 64)->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'destination_interface')) {
                $table->string('destination_interface', 64)->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'pckt_len')) {
                $table->unsignedInteger('pckt_len')->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'ttl')) {
                $table->unsignedInteger('ttl')->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'sent_bytes')) {
                $table->unsignedBigInteger('sent_bytes')->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'rcvd_bytes')) {
                $table->unsignedBigInteger('rcvd_bytes')->nullable();
            }

            if (! Schema::hasColumn('detection_results', 'geo_dst')) {
                $table->string('geo_dst', 16)->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('detection_results')) {
            return;
        }

        $columns = array_values(array_filter([
            Schema::hasColumn('detection_results', 'sn') ? 'sn' : null,
            Schema::hasColumn('detection_results', 'source_interface') ? 'source_interface' : null,
            Schema::hasColumn('detection_results', 'destination_interface') ? 'destination_interface' : null,
            Schema::hasColumn('detection_results', 'pckt_len') ? 'pckt_len' : null,
            Schema::hasColumn('detection_results', 'ttl') ? 'ttl' : null,
            Schema::hasColumn('detection_results', 'sent_bytes') ? 'sent_bytes' : null,
            Schema::hasColumn('detection_results', 'rcvd_bytes') ? 'rcvd_bytes' : null,
            Schema::hasColumn('detection_results', 'geo_dst') ? 'geo_dst' : null,
        ]));

        if ($columns === []) {
            return;
        }

        Schema::table('detection_results', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
