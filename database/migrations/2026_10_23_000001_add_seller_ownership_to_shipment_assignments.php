<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipment_assignments', function (Blueprint $table) {
            // Legacy records stay unowned until reviewed; names/phones are not identity.
            $table->foreignId('seller_id')->nullable()->constrained('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shipment_assignments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seller_id');
        });
    }
};
