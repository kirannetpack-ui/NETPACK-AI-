<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_partner_fields_to_pickup_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            // Partner fields
            $table->foreignId('partner_id')->nullable()->after('seller_id')->constrained('domestic_partners')->onDelete('set null');
            $table->foreignId('partner_staff_id')->nullable()->after('partner_id')->constrained('partner_staff')->onDelete('set null');
            
            // Status tracking fields
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('departed_at')->nullable();
            $table->json('status_history')->nullable();
            
            // Location fields
            $table->string('pickup_city')->nullable();
            $table->string('delivery_city')->nullable();
            $table->decimal('pickup_latitude', 10, 8)->nullable();
            $table->decimal('pickup_longitude', 11, 8)->nullable();
            $table->decimal('delivery_latitude', 10, 8)->nullable();
            $table->decimal('delivery_longitude', 11, 8)->nullable();
            $table->decimal('distance_km', 10, 2)->nullable();
            $table->decimal('calculated_price_final', 10, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropForeign(['partner_id']);
            $table->dropForeign(['partner_staff_id']);
            $table->dropColumn([
                'partner_id', 'partner_staff_id', 'arrived_at', 'departed_at',
                'status_history', 'pickup_city', 'delivery_city',
                'pickup_latitude', 'pickup_longitude', 'delivery_latitude',
                'delivery_longitude', 'distance_km', 'calculated_price_final'
            ]);
        });
    }
};