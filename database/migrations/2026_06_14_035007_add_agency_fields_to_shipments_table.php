<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_agency_fields_to_shipments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('current_agency_id')->nullable()->constrained('agencies')->onDelete('set null');
            $table->timestamp('arrived_at_agency')->nullable();
            $table->timestamp('departed_from_agency')->nullable();
            $table->json('agency_status_history')->nullable();
        });
    }

    public function down()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['current_agency_id']);
            $table->dropColumn(['current_agency_id', 'arrived_at_agency', 'departed_from_agency', 'agency_status_history']);
        });
    }
};