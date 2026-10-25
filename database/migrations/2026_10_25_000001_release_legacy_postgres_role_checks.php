<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Laravel's enum-to-string migrations leave PostgreSQL check constraints
     * behind on older schema versions. The operational services already own
     * these vocabularies, so remove only the obsolete database constraints.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ([
            'users' => 'users_role_check',
            'shipments' => 'shipments_status_check',
            'domestic_shipments' => 'domestic_shipments_status_check',
            'pickup_requests' => 'pickup_requests_status_check',
        ] as $table => $constraint) {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$constraint}");
        }
    }

    public function down(): void
    {
        // Narrowing an operational role or status vocabulary can discard valid
        // data, so the migration deliberately has no destructive rollback.
    }
};
