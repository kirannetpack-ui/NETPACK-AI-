<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'delivery_type')) {
                $table->string('delivery_type')->default('single')->after('shipping_address');
            }
            if (!Schema::hasColumn('orders', 'delivery_count')) {
                $table->integer('delivery_count')->default(1)->after('delivery_type');
            }
            if (!Schema::hasColumn('orders', 'delivery_data')) {
                $table->json('delivery_data')->nullable()->after('delivery_count');
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_type', 'delivery_count', 'delivery_data']);
        });
    }
};