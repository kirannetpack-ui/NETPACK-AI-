<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('logistics_services')) {
            Schema::create('logistics_services', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->enum('category', ['domestic', 'international', 'ecommerce'])->default('domestic');
                $table->decimal('transit_time_hours', 8, 2)->default(24);
                $table->decimal('transit_time_days', 8, 2)->nullable();
                $table->json('reminder_intervals')->nullable(); // e.g. [50, 75, 90] percentage checkpoints
                $table->decimal('base_rate', 10, 2)->nullable()->default(0);
                $table->decimal('per_kg_rate', 10, 2)->nullable()->default(0);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index(['category', 'is_active']);
                $table->index('code');
            });

            // Pre-seed standard logistics services across Domestic, E-Commerce, and International
            $now = now();
            $services = [
                // Domestic Services
                [
                    'code' => 'flash',
                    'name' => 'Flash Express (2-4h)',
                    'category' => 'domestic',
                    'transit_time_hours' => 4.00,
                    'transit_time_days' => 0.17,
                    'reminder_intervals' => json_encode([50, 75]),
                    'base_rate' => 200.00,
                    'per_kg_rate' => 80.00,
                    'description' => 'Ultra fast delivery within major valley wards within 2 to 4 hours.',
                    'is_active' => true,
                    'sort_order' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'same_day',
                    'name' => 'Same Day Courier (by 8 PM)',
                    'category' => 'domestic',
                    'transit_time_hours' => 12.00,
                    'transit_time_days' => 0.50,
                    'reminder_intervals' => json_encode([50, 75, 90]),
                    'base_rate' => 120.00,
                    'per_kg_rate' => 40.00,
                    'description' => 'Delivery on the same day for orders placed before noon.',
                    'is_active' => true,
                    'sort_order' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'standard',
                    'name' => 'Domestic Standard (1-3 Days)',
                    'category' => 'domestic',
                    'transit_time_hours' => 72.00,
                    'transit_time_days' => 3.00,
                    'reminder_intervals' => json_encode([33, 66, 90]),
                    'base_rate' => 80.00,
                    'per_kg_rate' => 30.00,
                    'description' => 'Standard inter-city parcel shipping across all provincial hubs.',
                    'is_active' => true,
                    'sort_order' => 3,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'himalayan',
                    'name' => 'Himalayan Remote (3-7 Days)',
                    'category' => 'domestic',
                    'transit_time_hours' => 168.00,
                    'transit_time_days' => 7.00,
                    'reminder_intervals' => json_encode([25, 50, 75, 90]),
                    'base_rate' => 250.00,
                    'per_kg_rate' => 120.00,
                    'description' => 'Dedicated mountain logistics for high-altitude remote districts.',
                    'is_active' => true,
                    'sort_order' => 4,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],

                // E-Commerce Services
                [
                    'code' => 'ecommerce',
                    'name' => 'E-Commerce Instant Dispatch (1 Hour)',
                    'category' => 'ecommerce',
                    'transit_time_hours' => 1.00,
                    'transit_time_days' => 0.04,
                    'reminder_intervals' => json_encode([50]),
                    'base_rate' => 150.00,
                    'per_kg_rate' => 50.00,
                    'description' => 'Instant merchant pick-to-drop service within central delivery zones.',
                    'is_active' => true,
                    'sort_order' => 5,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'ecommerce_standard',
                    'name' => 'E-Commerce Next-Day Fulfillment',
                    'category' => 'ecommerce',
                    'transit_time_hours' => 24.00,
                    'transit_time_days' => 1.00,
                    'reminder_intervals' => json_encode([50, 75, 90]),
                    'base_rate' => 90.00,
                    'per_kg_rate' => 35.00,
                    'description' => 'Standard next-day merchant delivery with cash-on-delivery collection.',
                    'is_active' => true,
                    'sort_order' => 6,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'grocery_fresh',
                    'name' => 'Fresh Grocery & Perishables (3 Hours)',
                    'category' => 'ecommerce',
                    'transit_time_hours' => 3.00,
                    'transit_time_days' => 0.12,
                    'reminder_intervals' => json_encode([50, 80]),
                    'base_rate' => 180.00,
                    'per_kg_rate' => 60.00,
                    'description' => 'Temperature-sensitive cold-chain transport for daily groceries.',
                    'is_active' => true,
                    'sort_order' => 7,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],

                // International Services
                [
                    'code' => 'air_cargo_express',
                    'name' => 'International Air Priority (72 Hours)',
                    'category' => 'international',
                    'transit_time_hours' => 72.00,
                    'transit_time_days' => 3.00,
                    'reminder_intervals' => json_encode([33, 66, 90]),
                    'base_rate' => 2500.00,
                    'per_kg_rate' => 850.00,
                    'description' => 'Expedited worldwide air freight with priority customs clearance.',
                    'is_active' => true,
                    'sort_order' => 8,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'air_freight_standard',
                    'name' => 'International Air Economy (5 Days)',
                    'category' => 'international',
                    'transit_time_hours' => 120.00,
                    'transit_time_days' => 5.00,
                    'reminder_intervals' => json_encode([30, 60, 90]),
                    'base_rate' => 1800.00,
                    'per_kg_rate' => 600.00,
                    'description' => 'Cost-effective global air delivery via overseas transit hubs.',
                    'is_active' => true,
                    'sort_order' => 9,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'sea_freight_economy',
                    'name' => 'Sea / Overland Cargo (30 Days)',
                    'category' => 'international',
                    'transit_time_hours' => 720.00,
                    'transit_time_days' => 30.00,
                    'reminder_intervals' => json_encode([25, 50, 75, 90]),
                    'base_rate' => 5000.00,
                    'per_kg_rate' => 250.00,
                    'description' => 'High-capacity container and consolidated sea freight.',
                    'is_active' => true,
                    'sort_order' => 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            DB::table('logistics_services')->insert($services);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistics_services');
    }
};
