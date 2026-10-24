<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('remote_area_surcharges', function (Blueprint $table) {
            if (!Schema::hasColumn('remote_area_surcharges', 'source')) {
                $table->string('source')->default('manual');
            }
            if (!Schema::hasColumn('remote_area_surcharges', 'source_updated_at')) {
                $table->timestamp('source_updated_at')->nullable();
            }
            if (!Schema::hasColumn('remote_area_surcharges', 'service_conditions')) {
                $table->text('service_conditions')->nullable();
            }
            if (!Schema::hasColumn('remote_area_surcharges', 'currency')) {
                $table->string('currency', 3)->default('USD');
            }
        });
    }

    public function down(): void
    {
        // Do not discard manually maintained surcharge provenance on rollback.
    }
};
