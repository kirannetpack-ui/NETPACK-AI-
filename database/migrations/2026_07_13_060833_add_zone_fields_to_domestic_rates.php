<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('domestic_rates', function (Blueprint $table) {
            if (!Schema::hasColumn('domestic_rates', 'origin_zone_id')) {
                $table->foreignId('origin_zone_id')->nullable()->after('partner_id')->constrained('delivery_zones')->onDelete('set null');
            }
            if (!Schema::hasColumn('domestic_rates', 'destination_zone_id')) {
                $table->foreignId('destination_zone_id')->nullable()->after('origin_zone_id')->constrained('delivery_zones')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('domestic_rates', function (Blueprint $table) {
            $table->dropForeign(['origin_zone_id']);
            $table->dropForeign(['destination_zone_id']);
            $table->dropColumn(['origin_zone_id', 'destination_zone_id']);
        });
    }
};