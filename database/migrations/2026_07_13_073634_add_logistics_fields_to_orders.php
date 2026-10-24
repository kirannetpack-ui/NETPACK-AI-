<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'client_id')) {
                $table->foreignId('client_id')->nullable()->after('customer_id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('orders', 'shipping_address')) {
                $table->text('shipping_address')->nullable()->after('customer_address');
            }
            if (!Schema::hasColumn('orders', 'delivery_date')) {
                $table->dateTime('delivery_date')->nullable()->after('shipped_at');
            }
            if (!Schema::hasColumn('orders', 'delivery_latitude')) {
                $table->decimal('delivery_latitude', 10, 8)->nullable()->after('shipping_address');
            }
            if (!Schema::hasColumn('orders', 'delivery_longitude')) {
                $table->decimal('delivery_longitude', 11, 8)->nullable()->after('delivery_latitude');
            }
            if (!Schema::hasColumn('orders', 'rider_id')) {
                $table->foreignId('rider_id')->nullable()->after('client_id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('orders', 'rider_assigned_at')) {
                $table->timestamp('rider_assigned_at')->nullable()->after('rider_id');
            }
            if (!Schema::hasColumn('orders', 'picked_up_at')) {
                $table->timestamp('picked_up_at')->nullable()->after('rider_assigned_at');
            }
            if (!Schema::hasColumn('orders', 'out_for_delivery_at')) {
                $table->timestamp('out_for_delivery_at')->nullable()->after('picked_up_at');
            }
            if (!Schema::hasColumn('orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('out_for_delivery_at');
            }
            if (!Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number')->unique()->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('orders', 'delivery_time_slot')) {
                $table->string('delivery_time_slot')->nullable()->after('delivery_date');
            }
            if (!Schema::hasColumn('orders', 'rider_acceptance_time')) {
                $table->timestamp('rider_acceptance_time')->nullable()->after('rider_assigned_at');
            }
            if (!Schema::hasColumn('orders', 'distance')) {
                $table->decimal('distance', 10, 2)->nullable()->after('delivery_latitude');
            }
            if (!Schema::hasColumn('orders', 'estimated_time')) {
                $table->integer('estimated_time')->nullable()->after('distance'); // in minutes
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_latitude', 'delivery_longitude', 'rider_id',
                'rider_assigned_at', 'picked_up_at', 'out_for_delivery_at',
                'delivered_at', 'tracking_number', 'delivery_time_slot',
                'rider_acceptance_time', 'distance', 'estimated_time'
            ]);
        });
    }
};
