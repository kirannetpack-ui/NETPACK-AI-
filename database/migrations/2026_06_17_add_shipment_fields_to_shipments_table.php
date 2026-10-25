<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Additional sender fields
            $table->decimal('sender_lat', 10, 8)->nullable()->after('sender_address');
            $table->decimal('sender_lng', 11, 8)->nullable()->after('sender_lat');
            
            // Additional receiver fields for international
            $table->string('receiver_state')->nullable()->after('receiver_city');
            $table->string('receiver_company')->nullable()->after('receiver_postal_code');
            $table->string('receiver_tax_id')->nullable()->after('receiver_company');
            $table->decimal('receiver_lat', 10, 8)->nullable()->after('receiver_tax_id');
            $table->decimal('receiver_lng', 11, 8)->nullable()->after('receiver_lat');
            
            // Multiple pickup and delivery points (JSON)
            $table->json('pickup_points')->nullable()->after('receiver_lng');
            $table->json('delivery_points')->nullable()->after('pickup_points');
            
            // Package dimensions
            $table->decimal('length', 8, 2)->nullable()->after('volumetric_weight');
            $table->decimal('width', 8, 2)->nullable()->after('length');
            $table->decimal('height', 8, 2)->nullable()->after('width');
            
            // Package description and type
            $table->text('description')->nullable()->after('chargeable_weight');
            $table->string('package_type')->nullable()->after('description');
            
            // E-commerce specific fields
            $table->string('order_id')->nullable()->after('package_type');
            $table->string('store_name')->nullable()->after('order_id');
            
            // Discount and tracking timeline
            $table->decimal('discount', 12, 2)->default(0)->after('total_amount');
            $table->json('tracking_timeline')->nullable()->after('tracking_history');
            
        });

        $statuses = [
            'pending', 'confirmed', 'processing', 'picked_up',
            'in_transit', 'customs_clearance', 'out_for_delivery',
            'delivered', 'returned', 'cancelled', 'failed',
        ];

        if (DB::getDriverName() === 'pgsql') {
            // PostgreSQL represents Laravel enums as a varchar check constraint.
            // Rebuild that constraint instead of issuing MySQL-style enum change SQL.
            DB::statement('ALTER TABLE shipments DROP CONSTRAINT IF EXISTS shipments_status_check');
            DB::statement("ALTER TABLE shipments ADD CONSTRAINT shipments_status_check CHECK (status IN ('".implode("', '", $statuses)."'))");
        } else {
            Schema::table('shipments', function (Blueprint $table) use ($statuses) {
                $table->enum('status', $statuses)->default('pending')->change();
            });
        }
    }

    public function down()
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Drop the added columns
            $table->dropColumn([
                'sender_lat',
                'sender_lng',
                'receiver_state',
                'receiver_company',
                'receiver_tax_id',
                'receiver_lat',
                'receiver_lng',
                'pickup_points',
                'delivery_points',
                'length',
                'width',
                'height',
                'description',
                'package_type',
                'order_id',
                'store_name',
                'discount',
                'tracking_timeline'
            ]);
        });
    }
};
