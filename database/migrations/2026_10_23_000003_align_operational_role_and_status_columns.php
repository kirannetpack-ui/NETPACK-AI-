<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $original = [
        'users' => ['column' => 'role', 'default' => 'admin', 'values' => ['super_admin', 'admin', 'manager', 'support']],
        'shipments' => ['column' => 'status', 'default' => 'pending', 'values' => ['pending', 'confirmed', 'processing', 'picked_up', 'in_transit', 'customs_clearance', 'out_for_delivery', 'delivered', 'returned', 'cancelled', 'failed']],
        'domestic_shipments' => ['column' => 'status', 'default' => 'pending', 'values' => ['pending', 'confirmed', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'failed_delivery', 'returned', 'cancelled']],
        'pickup_requests' => ['column' => 'status', 'default' => 'pending', 'values' => ['pending', 'assigned', 'picked_up', 'in_transit', 'delivered', 'cancelled']],
    ];

    public function up(): void
    {
        // Application validators and transition services own the vocabulary.
        // Old MySQL enums rejected rider/seller roles and legitimate scan states
        // that SQLite tests accepted. Existing values are preserved.
        foreach ($this->original as $name => $definition) {
            Schema::table($name, function (Blueprint $table) use ($name, $definition) {
                $table->string($definition['column'], 40)
                    ->default($name === 'users' ? 'customer' : $definition['default'])->change();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->original as $name => $definition) {
            if (DB::table($name)->whereNotIn($definition['column'], $definition['values'])->exists()) {
                throw new RuntimeException('New operational roles or statuses must be reconciled before narrowing the schema.');
            }
        }
        foreach ($this->original as $name => $definition) {
            Schema::table($name, function (Blueprint $table) use ($definition) {
                $table->enum($definition['column'], $definition['values'])->default($definition['default'])->change();
            });
        }
    }
};
