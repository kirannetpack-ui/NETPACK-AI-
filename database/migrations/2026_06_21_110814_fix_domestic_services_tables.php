<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =============================================
        // 1. FIX DELIVERY ZONES TABLE
        // =============================================
        if (!Schema::hasTable('delivery_zones')) {
            Schema::create('delivery_zones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('partner_id')->constrained('domestic_partners')->onDelete('cascade');
                $table->string('zone_name');
                $table->string('zone_code')->unique();
                $table->enum('zone_type', ['urban', 'semi_urban', 'rural', 'hilly', 'himalayan']);
                $table->json('districts')->nullable();
                $table->json('municipalities')->nullable();
                $table->json('wards')->nullable();
                $table->json('postal_codes')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['partner_id', 'zone_code']);
                $table->index('is_active');
            });
        }

        // =============================================
        // 2. CREATE DOMESTIC RATES TABLE (ZONE BASED)
        // =============================================
        if (!Schema::hasTable('domestic_rates')) {
            Schema::create('domestic_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('partner_id')->constrained('domestic_partners')->onDelete('cascade');
                $table->foreignId('origin_zone_id')->constrained('delivery_zones')->onDelete('cascade');
                $table->foreignId('destination_zone_id')->constrained('delivery_zones')->onDelete('cascade');
                
                // Service Type - FLASH, SAME DAY, STANDARD, HIMALAYAN
                $table->enum('service_type', ['flash', 'same_day', 'standard', 'himalayan']);
                $table->string('service_name'); // Display name
                
                // Pricing
                $table->decimal('base_rate', 10, 2);
                $table->decimal('per_kg_rate', 10, 2);
                $table->decimal('per_km_rate', 10, 2)->default(0);
                $table->decimal('minimum_rate', 10, 2)->default(0);
                $table->decimal('logistical_charge', 10, 2)->default(0);
                $table->decimal('additional_charge', 10, 2)->default(0);
                $table->text('additional_charge_reason')->nullable();
                
                // Weight ranges
                $table->decimal('weight_from', 10, 2)->default(0);
                $table->decimal('weight_to', 10, 2);
                
                // Delivery estimates
                $table->integer('estimated_hours')->nullable(); // For FLASH
                $table->integer('estimated_days')->nullable(); // For STANDARD & HIMALAYAN
                $table->integer('estimated_km')->nullable();
                
                // Status
                $table->boolean('is_active')->default(true);
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                
                $table->timestamps();
                
                // Indexes
                $table->index(['partner_id', 'service_type']);
                $table->index(['origin_zone_id', 'destination_zone_id']);
                $table->index('is_active');
                
                // Unique constraint
                $table->unique(['partner_id', 'origin_zone_id', 'destination_zone_id', 'service_type', 'weight_from', 'weight_to'], 'unique_domestic_rate');
            });
        }

        // =============================================
        // 3. CREATE DOMESTIC SHIPMENTS TABLE
        // =============================================
        if (!Schema::hasTable('domestic_shipments')) {
            Schema::create('domestic_shipments', function (Blueprint $table) {
                $table->id();
                $table->string('tracking_number')->unique();
                $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('partner_id')->constrained('domestic_partners')->onDelete('cascade');
                $table->foreignId('domestic_rate_id')->nullable()->constrained('domestic_rates')->onDelete('set null');
                
                // Sender Details
                $table->string('sender_name');
                $table->string('sender_email')->nullable();
                $table->string('sender_phone');
                $table->text('sender_address');
                $table->string('sender_city');
                $table->string('sender_zone')->nullable();
                $table->decimal('sender_lat', 10, 8)->nullable();
                $table->decimal('sender_lng', 11, 8)->nullable();
                
                // Receiver Details
                $table->string('receiver_name');
                $table->string('receiver_email')->nullable();
                $table->string('receiver_phone');
                $table->text('receiver_address');
                $table->string('receiver_city');
                $table->string('receiver_zone')->nullable();
                $table->string('receiver_ward')->nullable();
                $table->decimal('receiver_lat', 10, 8)->nullable();
                $table->decimal('receiver_lng', 11, 8)->nullable();
                
                // Package Details
                $table->decimal('weight', 10, 2);
                $table->decimal('length', 10, 2)->nullable();
                $table->decimal('width', 10, 2)->nullable();
                $table->decimal('height', 10, 2)->nullable();
                $table->string('package_type')->default('box');
                $table->text('package_description')->nullable();
                $table->text('special_instructions')->nullable();
                
                // Service Details
                $table->enum('service_type', ['flash', 'same_day', 'standard', 'himalayan']);
                $table->string('service_name');
                
                // Financial Details
                $table->decimal('base_rate', 10, 2);
                $table->decimal('per_kg_rate', 10, 2);
                $table->decimal('logistical_charge', 10, 2)->default(0);
                $table->decimal('additional_charge', 10, 2)->default(0);
                $table->decimal('total_amount', 10, 2);
                $table->string('currency')->default('NPR');
                
                // Delivery Estimates
                $table->integer('estimated_hours')->nullable();
                $table->integer('estimated_days')->nullable();
                $table->timestamp('estimated_delivery_at')->nullable();
                $table->timestamp('actual_delivery_at')->nullable();
                
                // Status
                $table->enum('status', [
                    'pending', 'confirmed', 'picked_up', 'in_transit',
                    'out_for_delivery', 'delivered', 'failed_delivery',
                    'returned', 'cancelled'
                ])->default('pending');
                
                $table->json('tracking_history')->nullable();
                $table->text('notes')->nullable();
                $table->text('delivery_notes')->nullable();
                
                // Documents
                $table->string('invoice_file')->nullable();
                $table->string('label_file')->nullable();
                $table->string('proof_of_delivery')->nullable();
                
                // Additional Features
                $table->boolean('requires_signature')->default(false);
                $table->boolean('is_insured')->default(false);
                $table->decimal('insurance_amount', 10, 2)->default(0);
                $table->boolean('is_cod')->default(false);
                $table->decimal('cod_amount', 10, 2)->default(0);
                
                $table->timestamps();
                
                $table->index('tracking_number');
                $table->index('client_id');
                $table->index('partner_id');
                $table->index('status');
                $table->index('service_type');
            });
        }

        // =============================================
        // 4. CREATE DOMESTIC TRACKING EVENTS
        // =============================================
        if (!Schema::hasTable('domestic_tracking_events')) {
            Schema::create('domestic_tracking_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('domestic_shipment_id')->constrained('domestic_shipments')->onDelete('cascade');
                $table->string('status');
                $table->string('location')->nullable();
                $table->string('city')->nullable();
                $table->string('zone')->nullable();
                $table->text('description')->nullable();
                $table->json('additional_data')->nullable();
                $table->timestamp('event_time');
                $table->timestamps();
                
                $table->index('domestic_shipment_id');
                $table->index('status');
            });
        }

        // =============================================
        // 5. CREATE RIDER DELIVERY ASSIGNMENTS
        // =============================================
        if (!Schema::hasTable('rider_delivery_assignments')) {
            Schema::create('rider_delivery_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rider_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('domestic_shipment_id')->constrained('domestic_shipments')->onDelete('cascade');
                $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
                
                $table->timestamp('assigned_at');
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('picked_up_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                
                $table->enum('status', [
                    'assigned', 'accepted', 'picked_up', 'in_transit',
                    'delivered', 'failed', 'returned'
                ])->default('assigned');
                
                $table->text('notes')->nullable();
                $table->text('failure_reason')->nullable();
                
                $table->timestamps();
                
                $table->unique(['rider_id', 'domestic_shipment_id']);
                $table->index('rider_id');
                $table->index('domestic_shipment_id');
                $table->index('status');
            });
        }

        // =============================================
        // 6. CREATE DELIVERY REMINDERS
        // =============================================
        if (!Schema::hasTable('delivery_reminders')) {
            Schema::create('delivery_reminders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pickup_request_id')->constrained('pickup_requests')->onDelete('cascade');
                $table->string('service_tier')->nullable();
                $table->string('reminder_type');
                $table->integer('reminder_number')->default(1);
                $table->timestamp('scheduled_at');
                $table->timestamp('sent_at')->nullable();
                $table->boolean('is_sent')->default(false);
                $table->text('message')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['pickup_request_id', 'is_sent']);
                $table->index('scheduled_at');
            });
        }

        // =============================================
        // 7. CREATE REMINDER LOGS
        // =============================================
        if (!Schema::hasTable('reminder_logs')) {
            Schema::create('reminder_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pickup_request_id')->nullable()->constrained('pickup_requests')->onDelete('cascade');
                $table->foreignId('reminder_id')->nullable()->constrained('delivery_reminders')->onDelete('set null');
                $table->string('reminder_type');
                $table->string('sent_to')->nullable();
                $table->text('message');
                $table->string('channel')->default('database');
                $table->string('status')->default('sent');
                $table->timestamp('sent_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['pickup_request_id', 'reminder_type']);
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
        Schema::dropIfExists('delivery_reminders');
        Schema::dropIfExists('rider_delivery_assignments');
        Schema::dropIfExists('domestic_tracking_events');
        Schema::dropIfExists('domestic_shipments');
        Schema::dropIfExists('domestic_rates');
        Schema::dropIfExists('delivery_zones');
    }
};
