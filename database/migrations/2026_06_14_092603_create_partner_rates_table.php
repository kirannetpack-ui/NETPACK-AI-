<?php
// database/migrations/xxxx_xx_xx_000003_create_partner_rates_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('partner_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('domestic_partners')->onDelete('cascade');
            $table->foreignId('zone_id')->constrained('partner_zones')->onDelete('cascade');
            $table->enum('service_tier', ['flash', 'same_day', 'standard', 'himalayan']);
            $table->decimal('base_rate', 10, 2);
            $table->decimal('per_kg_rate', 10, 2);
            $table->decimal('per_km_rate', 10, 2);
            $table->decimal('logistical_charge', 10, 2)->default(0);
            $table->decimal('additional_charge', 10, 2)->default(0);
            $table->text('additional_charge_reason')->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->integer('estimated_days')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('partner_rates');
    }
};