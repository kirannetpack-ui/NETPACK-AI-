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
        Schema::table('delivery_zones', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_zones', 'flash_base_rate')) {
                $table->decimal('flash_base_rate', 10, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('delivery_zones', 'flash_per_kg_rate')) {
                $table->decimal('flash_per_kg_rate', 10, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('delivery_zones', 'flash_estimated_hours')) {
                $table->unsignedInteger('flash_estimated_hours')->nullable()->default(4);
            }

            if (!Schema::hasColumn('delivery_zones', 'same_day_base_rate')) {
                $table->decimal('same_day_base_rate', 10, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('delivery_zones', 'same_day_per_kg_rate')) {
                $table->decimal('same_day_per_kg_rate', 10, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('delivery_zones', 'same_day_estimated_hours')) {
                $table->unsignedInteger('same_day_estimated_hours')->nullable()->default(12);
            }

            if (!Schema::hasColumn('delivery_zones', 'standard_base_rate')) {
                $table->decimal('standard_base_rate', 10, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('delivery_zones', 'standard_per_kg_rate')) {
                $table->decimal('standard_per_kg_rate', 10, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('delivery_zones', 'standard_estimated_hours')) {
                $table->unsignedInteger('standard_estimated_hours')->nullable()->default(24);
            }

            if (!Schema::hasColumn('delivery_zones', 'himalayan_base_rate')) {
                $table->decimal('himalayan_base_rate', 10, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('delivery_zones', 'himalayan_per_kg_rate')) {
                $table->decimal('himalayan_per_kg_rate', 10, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('delivery_zones', 'himalayan_estimated_hours')) {
                $table->unsignedInteger('himalayan_estimated_hours')->nullable()->default(72);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $columns = [
                'flash_base_rate', 'flash_per_kg_rate', 'flash_estimated_hours',
                'same_day_base_rate', 'same_day_per_kg_rate', 'same_day_estimated_hours',
                'standard_base_rate', 'standard_per_kg_rate', 'standard_estimated_hours',
                'himalayan_base_rate', 'himalayan_per_kg_rate', 'himalayan_estimated_hours',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('delivery_zones', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
