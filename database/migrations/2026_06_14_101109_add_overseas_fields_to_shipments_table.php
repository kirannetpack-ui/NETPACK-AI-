<?php
// database/migrations/xxxx_xx_xx_000004_add_overseas_fields_to_shipments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('overseas_partner_id')->nullable()->after('rider_id')->constrained('overseas_partners')->onDelete('set null');
            $table->foreignId('current_hub_id')->nullable()->after('overseas_partner_id')->constrained('overseas_hubs')->onDelete('set null');
            $table->timestamp('arrived_overseas_at')->nullable();
            $table->timestamp('departed_overseas_at')->nullable();
            $table->timestamp('customs_cleared_at')->nullable();
            $table->string('customs_status')->nullable();
            $table->json('overseas_tracking')->nullable();
        });
    }

    public function down()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['overseas_partner_id']);
            $table->dropForeign(['current_hub_id']);
            $table->dropColumn([
                'overseas_partner_id', 'current_hub_id', 'arrived_overseas_at',
                'departed_overseas_at', 'customs_cleared_at', 'customs_status', 'overseas_tracking'
            ]);
        });
    }
};