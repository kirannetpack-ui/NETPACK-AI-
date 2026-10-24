<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('hawb_number')->unique();
            $table->string('tracking_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('rider_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Sender (Nepal)
            $table->string('sender_name');
            $table->string('sender_phone');
            $table->string('sender_address');
            $table->string('sender_city')->default('Kathmandu');
            $table->string('sender_country')->default('Nepal');
            
            // Receiver (International)
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->string('receiver_address');
            $table->string('receiver_city');
            $table->string('receiver_country');
            $table->string('receiver_postal_code')->nullable();
            
            // Shipment Details
            $table->enum('service_type', ['economy', 'standard', 'express'])->default('standard');
            $table->enum('shipment_type', ['grocery', 'document', 'parcel'])->default('grocery');
            
            // Weight
            $table->decimal('actual_weight', 8, 2);
            $table->decimal('volumetric_weight', 8, 2)->nullable();
            $table->decimal('chargeable_weight', 8, 2);
            $table->json('boxes')->nullable();
            
            // Pricing
            $table->decimal('shipping_cost', 12, 2);
            $table->decimal('handling_fee', 12, 2)->default(0);
            $table->decimal('insurance_fee', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->enum('payment_method', ['online', 'cod'])->default('online');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            
            // Status
            $table->enum('status', [
                'pending', 'confirmed', 'processing', 'picked_up',
                'in_transit', 'customs_clearance', 'out_for_delivery',
                'delivered', 'returned', 'cancelled'
            ])->default('pending');
            
            // Tracking
            $table->json('tracking_history')->nullable();
            $table->timestamp('estimated_delivery')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('tracking_number');
            $table->index('hawb_number');
            $table->index('status');
            $table->index('customer_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipments');
    }
};