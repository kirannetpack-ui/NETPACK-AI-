<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // =============================================
        // 1. OVERSEAS RATES TABLE
        // =============================================
        Schema::create('overseas_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overseas_partner_id')->constrained('users')->onDelete('cascade');
            $table->string('country_from')->default('Nepal');
            $table->string('country_to');
            $table->string('city_from')->nullable();
            $table->string('city_to')->nullable();
            $table->decimal('weight_from', 10, 2)->default(0);
            $table->decimal('weight_to', 10, 2);
            $table->decimal('rate_per_kg', 10, 2);
            $table->decimal('minimum_rate', 10, 2)->default(0);
            $table->string('service_type')->default('standard'); // express, standard, economy, priority
            $table->integer('transit_time')->nullable(); // in days
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index(['country_from', 'country_to']);
            $table->index(['weight_from', 'weight_to']);
            $table->index('overseas_partner_id');
            $table->index('service_type');
            $table->index('is_active');
        });

        // =============================================
        // 2. REMOTE AREA SURCHARGES TABLE
        // =============================================
        Schema::create('remote_area_surcharges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overseas_partner_id')->constrained('users')->onDelete('cascade');
            $table->string('country');
            $table->string('city')->nullable();
            $table->string('zip_code_pattern')->nullable();
            $table->string('area_name');
            $table->decimal('surcharge_amount', 10, 2)->default(0);
            $table->decimal('surcharge_percentage', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['country', 'zip_code_pattern']);
            $table->index('overseas_partner_id');
            $table->index('is_active');
        });

        // =============================================
        // 3. ADDITIONAL CHARGES TABLE
        // =============================================
        Schema::create('additional_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overseas_partner_id')->constrained('users')->onDelete('cascade');
            $table->string('charge_name');
            $table->enum('charge_type', ['percentage', 'fixed', 'per_kg']);
            $table->decimal('charge_value', 10, 2);
            $table->enum('applicable_to', ['all', 'specific_countries', 'specific_services']);
            $table->json('country_codes')->nullable();
            $table->json('service_types')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('overseas_partner_id');
            $table->index('charge_type');
            $table->index('is_active');
        });

        // =============================================
        // 4. INTERNATIONAL SHIPMENTS TABLE
        // =============================================
        Schema::create('international_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('overseas_partner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('overseas_rate_id')->nullable()->constrained('overseas_rates')->onDelete('set null');
            
            // Shipment Details
            $table->string('sender_name');
            $table->string('sender_email');
            $table->string('sender_phone');
            $table->text('sender_address');
            $table->string('sender_country');
            $table->string('sender_city')->nullable();
            $table->string('sender_zip')->nullable();
            
            $table->string('receiver_name');
            $table->string('receiver_email')->nullable();
            $table->string('receiver_phone');
            $table->text('receiver_address');
            $table->string('receiver_country');
            $table->string('receiver_city')->nullable();
            $table->string('receiver_zip')->nullable();
            
            // Package Details
            $table->decimal('weight', 10, 2);
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->string('package_type')->default('box');
            $table->text('package_description')->nullable();
            
            // Financial Details
            $table->decimal('base_rate', 10, 2);
            $table->decimal('surcharge_amount', 10, 2)->default(0);
            $table->decimal('additional_charges', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency')->default('USD');
            
            // Service Details
            $table->string('service_type')->default('standard');
            $table->integer('estimated_days')->nullable();
            $table->date('estimated_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            
            // Status & Tracking
            $table->enum('status', [
                'pending',
                'confirmed',
                'picked_up',
                'in_transit',
                'customs_clearance',
                'out_for_delivery',
                'delivered',
                'failed_delivery',
                'returned',
                'cancelled'
            ])->default('pending');
            
            $table->json('tracking_history')->nullable();
            $table->text('notes')->nullable();
            
            // Documents
            $table->string('invoice_file')->nullable();
            $table->string('customs_declaration_file')->nullable();
            $table->string('label_file')->nullable();
            
            // Additional Info
            $table->boolean('is_remote_area')->default(false);
            $table->boolean('requires_signature')->default(false);
            $table->boolean('insurance_applied')->default(false);
            $table->decimal('insurance_amount', 10, 2)->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('tracking_number');
            $table->index('client_id');
            $table->index('overseas_partner_id');
            $table->index('status');
            $table->index('receiver_country');
            $table->index('created_at');
        });

        // =============================================
        // 5. INTERNATIONAL TRACKING EVENTS TABLE
        // =============================================
        Schema::create('international_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('international_shipment_id')->constrained('international_shipments')->onDelete('cascade');
            $table->string('status');
            $table->string('location')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->text('description')->nullable();
            $table->json('additional_data')->nullable();
            $table->timestamp('event_time');
            $table->timestamps();
            
            // Indexes
            $table->index('international_shipment_id');
            $table->index('status');
            $table->index('event_time');
        });

        // =============================================
        // 6. DOMESTIC RATES TABLE
        // =============================================
        Schema::create('domestic_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('origin_city');
            $table->string('origin_zone')->nullable();
            $table->string('destination_city');
            $table->string('destination_zone')->nullable();
            $table->decimal('weight_from', 10, 2)->default(0);
            $table->decimal('weight_to', 10, 2);
            $table->decimal('rate_per_kg', 10, 2);
            $table->decimal('minimum_rate', 10, 2)->default(0);
            $table->string('service_type')->default('standard');
            $table->integer('estimated_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['origin_city', 'destination_city']);
            $table->index(['weight_from', 'weight_to']);
            $table->index('partner_id');
            $table->index('is_active');
        });

        // =============================================
        // 7. DELIVERY ZONES TABLE
        // =============================================
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->string('zone_name');
            $table->string('zone_code')->unique();
            $table->string('zone_type')->default('domestic'); // domestic, international
            $table->json('cities')->nullable();
            $table->json('postal_codes')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('zone_code');
            $table->index('zone_type');
            $table->index('is_active');
        });

        // =============================================
        // 8. OVERSEAS PARTNER SETTINGS TABLE
        // =============================================
        Schema::create('overseas_partner_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overseas_partner_id')->constrained('users')->onDelete('cascade');
            $table->string('setting_key');
            $table->text('setting_value')->nullable();
            $table->enum('value_type', ['string', 'integer', 'boolean', 'json'])->default('string');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->unique(['overseas_partner_id', 'setting_key']);
            $table->index('overseas_partner_id');
            $table->index('setting_key');
        });

        // =============================================
        // 9. RATE SHEET IMPORTS LOG TABLE
        // =============================================
        Schema::create('rate_sheet_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overseas_partner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('imported_by')->constrained('users')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->default('excel'); // excel, csv, json
            $table->integer('total_records')->default(0);
            $table->integer('imported_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->json('failed_rows')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('overseas_partner_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_sheet_imports');
        Schema::dropIfExists('overseas_partner_settings');
        Schema::dropIfExists('delivery_zones');
        Schema::dropIfExists('domestic_rates');
        Schema::dropIfExists('international_tracking_events');
        Schema::dropIfExists('international_shipments');
        Schema::dropIfExists('additional_charges');
        Schema::dropIfExists('remote_area_surcharges');
        Schema::dropIfExists('overseas_rates');
    }
};