<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. International Zones Table
        Schema::create('international_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Zone 1 - Gulf / Middle East
            $table->string('code')->unique(); // e.g. ZONE_GULF
            $table->json('countries'); // JSON array of country names
            $table->foreignId('hub_id')->nullable()->constrained('overseas_hubs')->onDelete('set null');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });

        // 2. International Rates Matrix Table
        Schema::create('international_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('hub_id')->nullable()->constrained('overseas_hubs')->onDelete('set null');
            $table->string('service_type')->default('express'); // express, economy
            $table->string('rate_type')->default('country'); // country, zone
            $table->string('country')->nullable(); // For country-wise rate
            $table->string('country_code', 10)->nullable();
            $table->foreignId('zone_id')->nullable()->constrained('international_zones')->onDelete('cascade');
            
            // Weight slabs from 0.5kg to 10.0kg in 0.5kg steps (Key-value JSON: {"0.5": 2200, "1.0": 2800, ...})
            $table->json('weight_tiers')->nullable();
            
            // Dynamic per-kg ranges above 10kg (JSON array: [{"min_weight": 10.1, "max_weight": 20.0, "rate_per_kg": 1200}, ...])
            $table->json('per_kg_tiers')->nullable();
            
            // Standard default charges
            $table->decimal('customs_clearance_charge', 10, 2)->default(500.00);
            $table->decimal('godown_charge', 10, 2)->default(300.00); // Warehouse / storage / handling charge
            $table->decimal('fuel_surcharge_percent', 5, 2)->default(0.00);
            $table->decimal('doc_fee', 10, 2)->default(0.00);
            
            $table->integer('transit_days_min')->default(3);
            $table->integer('transit_days_max')->default(7);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['rate_type', 'country']);
            $table->index(['service_type', 'is_active']);
            $table->index('hub_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('international_rates');
        Schema::dropIfExists('international_zones');
    }
};
