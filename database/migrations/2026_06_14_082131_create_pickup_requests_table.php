<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_pickup_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pickup_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users');
            $table->foreignId('assigned_rider_id')->nullable()->constrained('users');
            
            // Pickup details
            $table->string('pickup_address');
            $table->string('pickup_ward_no');
            $table->string('pickup_municipality');
            $table->string('pickup_district');
            $table->string('pickup_province');
            
            // Delivery details
            $table->string('delivery_address');
            $table->string('delivery_ward_no');
            $table->string('delivery_municipality');
            $table->string('delivery_district');
            $table->string('delivery_province');
            
            $table->datetime('scheduled_pickup_time');
            $table->datetime('picked_up_at')->nullable();
            $table->datetime('delivered_at')->nullable();
            
            $table->text('items_description');
            $table->decimal('estimated_weight_kg', 8, 2);
            $table->decimal('actual_weight_kg', 8, 2)->nullable();
            
            $table->enum('service_tier', ['flash', 'same_day', 'standard', 'himalayan'])->default('standard');
            $table->enum('status', ['pending', 'assigned', 'picked_up', 'in_transit', 'delivered', 'cancelled'])->default('pending');
            
            $table->decimal('calculated_price', 10, 2)->nullable();
            $table->string('otp_code')->nullable();
            $table->string('delivery_proof_image')->nullable();
            $table->text('status_notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pickup_requests');
    }
};