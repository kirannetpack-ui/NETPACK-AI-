<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reconcile the early rate/zone schema with the production controllers.
     * Every addition is nullable or has a safe default so existing pricing and
     * zones remain available during deployment.
     */
    public function up(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_zones', 'partner_id')) {
                $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('delivery_zones', 'admin_id')) {
                $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('delivery_zones', 'districts')) {
                $table->json('districts')->nullable();
            }
            if (!Schema::hasColumn('delivery_zones', 'municipalities')) {
                $table->json('municipalities')->nullable();
            }
            if (!Schema::hasColumn('delivery_zones', 'wards')) {
                $table->json('wards')->nullable();
            }
        });

        Schema::table('domestic_rates', function (Blueprint $table) {
            if (!Schema::hasColumn('domestic_rates', 'service_name')) {
                $table->string('service_name')->nullable()->after('service_type');
            }
            if (!Schema::hasColumn('domestic_rates', 'base_rate')) {
                $table->decimal('base_rate', 10, 2)->default(0)->after('service_name');
            }
            if (!Schema::hasColumn('domestic_rates', 'per_kg_rate')) {
                $table->decimal('per_kg_rate', 10, 2)->default(0)->after('base_rate');
            }
            if (!Schema::hasColumn('domestic_rates', 'per_km_rate')) {
                $table->decimal('per_km_rate', 10, 2)->default(0)->after('per_kg_rate');
            }
            if (!Schema::hasColumn('domestic_rates', 'logistical_charge')) {
                $table->decimal('logistical_charge', 10, 2)->default(0)->after('minimum_rate');
            }
            if (!Schema::hasColumn('domestic_rates', 'additional_charge')) {
                $table->decimal('additional_charge', 10, 2)->default(0)->after('logistical_charge');
            }
            if (!Schema::hasColumn('domestic_rates', 'additional_charge_reason')) {
                $table->text('additional_charge_reason')->nullable()->after('additional_charge');
            }
            if (!Schema::hasColumn('domestic_rates', 'estimated_hours')) {
                $table->unsignedInteger('estimated_hours')->nullable()->after('estimated_days');
            }
            if (!Schema::hasColumn('domestic_rates', 'estimated_km')) {
                $table->unsignedInteger('estimated_km')->nullable()->after('estimated_hours');
            }
            if (!Schema::hasColumn('domestic_rates', 'currency')) {
                $table->string('currency', 3)->default('NPR')->after('additional_charge_reason');
            }
        });

        // Preserve historical prices by copying the original rate-per-kg
        // field into the canonical field used by the pricing engine.
        if (Schema::hasColumn('domestic_rates', 'rate_per_kg')) {
            DB::table('domestic_rates')->where('per_kg_rate', 0)->update([
                'per_kg_rate' => DB::raw('rate_per_kg'),
            ]);
        }
    }

    public function down(): void
    {
        // Deliberately non-destructive: removing these columns would discard
        // rates entered after this upgrade. Roll back the deployment instead.
    }
};
