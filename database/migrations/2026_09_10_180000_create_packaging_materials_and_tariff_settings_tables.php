<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Packaging Materials Table (Dynamic packaging options & charges)
        Schema::create('packaging_materials', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->string('icon', 50)->default('box');
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Global Tariff Settings Table (Dynamic baseline Customs Clearance & Godown charges fed by Super Admin)
        Schema::create('global_tariff_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100)->unique();
            $table->text('setting_value')->nullable();
            $table->string('display_name', 150);
            $table->text('description')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Seed initial dynamic packaging catalog
        $initialPackaging = [
            [
                'code' => 'none',
                'name' => 'No Packaging Required (Customer Pre-Packed)',
                'price' => 0.00,
                'description' => 'Shipper provides own IATA-compliant packaging.',
                'icon' => 'box',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'document_envelope',
                'name' => 'Reinforced Document Envelope / Waterproof Pouch',
                'price' => 150.00,
                'description' => 'Security-sealed, moisture-resistant envelope for legal and commercial papers.',
                'icon' => 'envelope-open-text',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'bubble_flyer',
                'name' => 'Tamper-Evident Bubble Flyer Bag',
                'price' => 200.00,
                'description' => 'Padded shock-absorbing poly flyer with tamper-evident seal.',
                'icon' => 'shield-halved',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'small_box',
                'name' => 'Small Corrugated Cargo Box (Up to 3 KG)',
                'price' => 350.00,
                'description' => 'Double-ply export box with corner edge protectors.',
                'icon' => 'box-archive',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'medium_box',
                'name' => 'Medium Air Cargo Box (3 KG to 10 KG)',
                'price' => 600.00,
                'description' => 'Heavy-duty 5-ply corrugated carton with internal bubble lining.',
                'icon' => 'boxes-stacked',
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'large_box',
                'name' => 'Heavy Duty Export Master Box (10 KG to 25 KG)',
                'price' => 1200.00,
                'description' => 'Export-grade reinforced double-wall carton with nylon strapping.',
                'icon' => 'dolly',
                'is_active' => true,
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'wooden_crate',
                'name' => 'Custom Wooden Crate / Palletized Protection',
                'price' => 3500.00,
                'description' => 'ISPM-15 heat-treated fumigated timber crating for high-value/fragile cargo.',
                'icon' => 'pallet',
                'is_active' => true,
                'sort_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('packaging_materials')->insert($initialPackaging);

        // Seed initial dynamic global tariff settings
        $initialSettings = [
            [
                'setting_key' => 'default_customs_clearance_charge',
                'setting_value' => '500.00',
                'display_name' => 'Default Customs Clearance Charge (NPR)',
                'description' => 'Standard origin export customs examination and declaration fee per consignment.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'default_godown_charge',
                'setting_value' => '300.00',
                'display_name' => 'Default Airport Godown / Terminal Handling Charge (NPR)',
                'description' => 'Tribhuvan International Airport (TIA) Cargo Complex terminal handling, screening, and godown storage fee.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'customs_charge_notice',
                'setting_value' => 'Mandatory origin customs clearance and export inspection fee conducted at Tribhuvan International Airport Cargo Customs desk.',
                'display_name' => 'Customs Clearance Explanatory Note for Clients',
                'description' => 'Customer-facing explanation displayed on rate quotation breakdowns.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'godown_charge_notice',
                'setting_value' => 'TIA Air Cargo Terminal godown handling, weighing, security screening, and pallet staging charge.',
                'display_name' => 'Godown Handling Explanatory Note for Clients',
                'description' => 'Customer-facing explanation displayed on rate quotation breakdowns.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('global_tariff_settings')->insert($initialSettings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_tariff_settings');
        Schema::dropIfExists('packaging_materials');
    }
};
