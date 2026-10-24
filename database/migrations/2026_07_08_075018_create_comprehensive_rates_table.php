<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Base Rates Table
        Schema::create('overseas_base_rates', function (Blueprint $table) {
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
            $table->string('service_type')->default('standard');
            $table->integer('transit_days')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->index(['overseas_partner_id', 'country_to']);
            $table->index(['weight_from', 'weight_to']);
            $table->index('is_active');
        });

        // 2. Additional Charges/Sub-rates Table
        Schema::create('overseas_sub_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overseas_partner_id')->constrained('users')->onDelete('cascade');
            $table->string('charge_name');
            $table->string('charge_code')->unique();
            $table->enum('charge_type', ['percentage', 'fixed', 'per_kg', 'per_shipment']);
            $table->decimal('charge_value', 10, 2);
            $table->decimal('minimum_charge', 10, 2)->default(0);
            $table->decimal('maximum_charge', 10, 2)->nullable();
            
            // Applicability conditions
            $table->json('applicable_countries')->nullable(); // ['USA', 'UK', 'ALL']
            $table->json('applicable_services')->nullable(); // ['express', 'standard']
            $table->decimal('applicable_weight_from', 10, 2)->nullable();
            $table->decimal('applicable_weight_to', 10, 2)->nullable();
            $table->decimal('applicable_value_from', 10, 2)->nullable();
            $table->decimal('applicable_value_to', 10, 2)->nullable();
            
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->index(['overseas_partner_id', 'charge_code']);
            $table->index('charge_type');
            $table->index('is_active');
        });

        // 3. Margin Rules Table
        Schema::create('overseas_margin_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overseas_partner_id')->constrained('users')->onDelete('cascade');
            $table->string('rule_name');
            $table->enum('margin_type', ['percentage', 'flat']);
            $table->decimal('margin_value', 10, 2);
            
            // Weight-based conditions
            $table->decimal('weight_from', 10, 2)->default(0);
            $table->decimal('weight_to', 10, 2)->nullable();
            
            // Country-based conditions
            $table->json('applicable_countries')->nullable(); // ['USA', 'UK', 'ALL']
            
            // Service-based conditions
            $table->json('applicable_services')->nullable(); // ['express', 'standard']
            
            $table->boolean('apply_to_sub_rates')->default(false);
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->index(['overseas_partner_id', 'margin_type']);
            $table->index('is_active');
        });

        // 4. Rate Sheet Import Logs
        Schema::create('rate_sheet_import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overseas_partner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('imported_by')->constrained('users')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->enum('import_type', ['base_rates', 'sub_rates', 'both']);
            $table->integer('total_records')->default(0);
            $table->integer('successful_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->json('failed_rows')->nullable();
            $table->json('summary')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['overseas_partner_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rate_sheet_import_logs');
        Schema::dropIfExists('overseas_margin_rules');
        Schema::dropIfExists('overseas_sub_rates');
        Schema::dropIfExists('overseas_base_rates');
    }
};