<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('seller_id')->constrained('orders')->nullOnDelete();
            $table->boolean('is_ecommerce')->default(false)->after('order_id');
            $table->string('platform', 50)->nullable();
            $table->string('order_reference')->nullable()->index();
            $table->string('tracking_number', 32)->nullable()->unique();
            $table->decimal('cod_amount', 12, 2)->default(0);
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->decimal('seller_earnings', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 30)->nullable();
            $table->string('customer_email')->nullable();
            $table->json('product_items')->nullable();
            $table->string('payment_status', 30)->nullable();
            $table->string('qr_code')->nullable();
            $table->string('delivery_label')->nullable();
            $table->timestamp('expected_resolution_time')->nullable();

            // Admin-created ecommerce pickups may not yet have full ward/package details.
            $table->string('pickup_ward_no')->nullable()->change();
            $table->string('pickup_municipality')->nullable()->change();
            $table->string('pickup_district')->nullable()->change();
            $table->string('pickup_province')->nullable()->change();
            $table->string('delivery_ward_no')->nullable()->change();
            $table->string('delivery_municipality')->nullable()->change();
            $table->string('delivery_district')->nullable()->change();
            $table->string('delivery_province')->nullable()->change();
            $table->text('items_description')->nullable()->change();
            $table->decimal('estimated_weight_kg', 8, 2)->nullable()->change();
            $table->string('service_tier', 30)->default('standard')->change();
        });
    }

    public function down(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropUnique(['tracking_number']);
            $table->dropColumn([
                'order_id', 'is_ecommerce', 'platform', 'order_reference', 'tracking_number',
                'cod_amount', 'platform_fee', 'seller_earnings', 'total_amount', 'customer_name',
                'customer_phone', 'customer_email', 'product_items', 'payment_status', 'qr_code',
                'delivery_label', 'expected_resolution_time',
            ]);
        });
    }
};
