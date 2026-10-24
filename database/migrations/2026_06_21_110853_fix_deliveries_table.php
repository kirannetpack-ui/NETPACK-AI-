<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the empty deliveries table if exists
        Schema::dropIfExists('deliveries');
        
        // Recreate with proper schema
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shipment_id')->nullable()->constrained('shipments')->onDelete('set null');
            $table->foreignId('pickup_request_id')->nullable()->constrained('pickup_requests')->onDelete('set null');
            
            // Delivery Details
            $table->string('delivery_type')->default('pickup'); // pickup, delivery, return
            $table->text('pickup_address')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('special_instructions')->nullable();
            
            // Status
            $table->enum('status', [
                'assigned', 'accepted', 'picked_up', 'in_transit',
                'arrived', 'delivered', 'failed', 'returned', 'cancelled'
            ])->default('assigned');
            
            // Timestamps
            $table->timestamp('assigned_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('in_transit_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            
            // Location Tracking
            $table->decimal('pickup_lat', 10, 8)->nullable();
            $table->decimal('pickup_lng', 11, 8)->nullable();
            $table->decimal('delivery_lat', 10, 8)->nullable();
            $table->decimal('delivery_lng', 11, 8)->nullable();
            $table->decimal('current_lat', 10, 8)->nullable();
            $table->decimal('current_lng', 11, 8)->nullable();
            
            // Financial
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('rider_earnings', 10, 2)->default(0);
            $table->decimal('cod_amount', 10, 2)->default(0);
            $table->boolean('is_cod')->default(false);
            
            // Documents
            $table->string('proof_of_delivery')->nullable();
            $table->string('delivery_signature')->nullable();
            
            // Notes
            $table->text('delivery_notes')->nullable();
            $table->text('failure_reason')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('rider_id');
            $table->index('status');
            $table->index('delivery_type');
            $table->index('assigned_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};