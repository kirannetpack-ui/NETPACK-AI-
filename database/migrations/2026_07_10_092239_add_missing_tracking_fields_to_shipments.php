<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Current location fields
            if (!Schema::hasColumn('shipments', 'current_location')) {
                $table->string('current_location')->nullable()->after('status');
            }
            if (!Schema::hasColumn('shipments', 'current_latitude')) {
                $table->decimal('current_latitude', 10, 8)->nullable()->after('current_location');
            }
            if (!Schema::hasColumn('shipments', 'current_longitude')) {
                $table->decimal('current_longitude', 11, 8)->nullable()->after('current_latitude');
            }
            if (!Schema::hasColumn('shipments', 'status_notes')) {
                $table->text('status_notes')->nullable()->after('tracking_history');
            }
            
            // Notes field
            if (!Schema::hasColumn('shipments', 'notes')) {
                $table->text('notes')->nullable()->after('store_name');
            }
            
            // Overseas partner
            if (!Schema::hasColumn('shipments', 'overseas_partner_id')) {
                $table->foreignId('overseas_partner_id')->nullable()->after('rider_id')->constrained('users')->onDelete('set null');
            }
            
            // Current hub
            if (!Schema::hasColumn('shipments', 'current_hub_id')) {
                $table->foreignId('current_hub_id')->nullable()->after('overseas_partner_id')->constrained('overseas_hubs')->onDelete('set null');
            }
            
            // Current transit point
            if (!Schema::hasColumn('shipments', 'current_transit_point_id')) {
                $table->foreignId('current_transit_point_id')->nullable()->after('current_hub_id')->constrained('overseas_transit_points')->onDelete('set null');
            }
            
            // Overseas tracking fields
            if (!Schema::hasColumn('shipments', 'arrived_overseas_at')) {
                $table->timestamp('arrived_overseas_at')->nullable()->after('overseas_tracking');
            }
            if (!Schema::hasColumn('shipments', 'departed_overseas_at')) {
                $table->timestamp('departed_overseas_at')->nullable()->after('arrived_overseas_at');
            }
            if (!Schema::hasColumn('shipments', 'customs_cleared_at')) {
                $table->timestamp('customs_cleared_at')->nullable()->after('departed_overseas_at');
            }
            if (!Schema::hasColumn('shipments', 'customs_status')) {
                $table->string('customs_status')->nullable()->after('customs_cleared_at');
            }
        });
    }

    public function down()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $columns = [
                'current_location',
                'current_latitude',
                'current_longitude',
                'status_notes',
                'notes',
                'overseas_partner_id',
                'current_hub_id',
                'current_transit_point_id',
                'arrived_overseas_at',
                'departed_overseas_at',
                'customs_cleared_at',
                'customs_status',
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('shipments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};