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
        if (Schema::hasTable('international_rates')) {
            Schema::table('international_rates', function (Blueprint $table) {
                if (!Schema::hasColumn('international_rates', 'agency_id')) {
                    $table->foreignId('agency_id')->nullable()->after('hub_id')->constrained('agencies')->onDelete('set null');
                    $table->index(['hub_id', 'agency_id']);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('international_rates')) {
            Schema::table('international_rates', function (Blueprint $table) {
                if (Schema::hasColumn('international_rates', 'agency_id')) {
                    $table->dropForeign(['agency_id']);
                    $table->dropColumn('agency_id');
                }
            });
        }
    }
};
