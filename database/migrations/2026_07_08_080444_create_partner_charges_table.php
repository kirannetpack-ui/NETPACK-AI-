<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Partner Charges Table
        Schema::create('partner_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('partner_id')->constrained('users')->onDelete('cascade');
            $table->string('shipment_reference')->nullable();
            
            // Charge Details
            $table->decimal('base_charge', 12, 2)->default(0);
            $table->decimal('weight_charge', 12, 2)->default(0);
            $table->decimal('distance_charge', 12, 2)->default(0);
            $table->decimal('additional_charges', 12, 2)->default(0);
            $table->decimal('fuel_surcharge', 12, 2)->default(0);
            $table->decimal('handling_fee', 12, 2)->default(0);
            $table->decimal('insurance_charge', 12, 2)->default(0);
            $table->decimal('customs_charge', 12, 2)->default(0);
            $table->decimal('total_charge', 12, 2)->default(0);
            
            // System Calculated Charges (for comparison)
            $table->decimal('system_base_charge', 12, 2)->nullable();
            $table->decimal('system_total_charge', 12, 2)->nullable();
            $table->decimal('charge_difference', 12, 2)->nullable();
            $table->decimal('charge_percentage_difference', 5, 2)->nullable();
            
            // Service Details
            $table->string('service_type')->nullable();
            $table->string('service_tier')->nullable();
            $table->decimal('weight_kg', 10, 2)->nullable();
            $table->integer('distance_km')->nullable();
            $table->integer('transit_days')->nullable();
            
            // Supporting Documents
            $table->string('invoice_file')->nullable();
            $table->string('supporting_document')->nullable();
            $table->json('additional_files')->nullable();
            
            // Charge Breakdown (JSON for flexibility)
            $table->json('charge_breakdown')->nullable();
            $table->json('system_breakdown')->nullable();
            
            // Status & Verification
            $table->enum('status', [
                'pending',           // Partner submitted, awaiting admin review
                'under_review',      // Admin is reviewing
                'verified',          // Admin verified and approved
                'disputed',          // Admin disputes the charge
                'adjusted',          // Charges were adjusted
                'rejected',          // Charges were rejected
                'approved'           // Charges approved for payment
            ])->default('pending');
            
            $table->enum('verification_status', [
                'pending',
                'verified',
                'disputed',
                'adjusted'
            ])->default('pending');
            
            $table->text('notes')->nullable();
            $table->text('dispute_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('adjustment_notes')->nullable();
            
            // Timestamps
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('disputed_at')->nullable();
            $table->timestamp('adjusted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            // Staff Information
            $table->foreignId('submitted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('disputed_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['shipment_id', 'partner_id']);
            $table->index('status');
            $table->index('verification_status');
            $table->index('submitted_at');
            $table->index('total_charge');
        });

        // 2. Partner Charge History Table
        Schema::create('partner_charge_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_charge_id')->constrained('partner_charges')->onDelete('cascade');
            $table->string('action');
            $table->text('notes')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('partner_charge_id');
            $table->index('action');
        });

        // 3. Partner Rate Verification Logs
        Schema::create('partner_rate_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_charge_id')->constrained('partner_charges')->onDelete('cascade');
            $table->decimal('partner_rate', 12, 2);
            $table->decimal('system_rate', 12, 2);
            $table->decimal('difference', 12, 2);
            $table->string('verification_type')->default('auto');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('partner_rate_verification_logs');
        Schema::dropIfExists('partner_charge_history');
        Schema::dropIfExists('partner_charges');
    }
};