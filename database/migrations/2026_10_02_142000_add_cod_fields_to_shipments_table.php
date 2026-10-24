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
        Schema::table('shipments', function (Blueprint $table) {
            if (!Schema::hasColumn('shipments', 'is_cod')) {
                $table->boolean('is_cod')->default(false)->after('payment_method');
            }
            if (!Schema::hasColumn('shipments', 'cod_amount')) {
                $table->decimal('cod_amount', 12, 2)->default(0)->after('is_cod');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            if (Schema::hasColumn('shipments', 'is_cod')) {
                $table->dropColumn('is_cod');
            }
            if (Schema::hasColumn('shipments', 'cod_amount')) {
                $table->dropColumn('cod_amount');
            }
        });
    }
};
