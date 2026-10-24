<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_intents', function (Blueprint $table) {
            $table->unsignedBigInteger('seller_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Customer-only captures cannot be assigned to a fabricated seller.
        if (DB::table('payment_intents')->whereNull('seller_id')->exists()) {
            throw new RuntimeException('Customer-only payment intents require reconciliation before rollback.');
        }
        Schema::table('payment_intents', function (Blueprint $table) {
            $table->unsignedBigInteger('seller_id')->nullable(false)->change();
        });
    }
};
