<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'cod_amount')) {
                $table->decimal('cod_amount', 12, 2)->nullable()->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'cod_invoice_file')) {
                $table->string('cod_invoice_file')->nullable()->after('cod_amount');
            }
            if (!Schema::hasColumn('orders', 'cod_collected_amount')) {
                $table->decimal('cod_collected_amount', 12, 2)->nullable()->after('cod_invoice_file');
            }
            if (!Schema::hasColumn('orders', 'cod_collected_at')) {
                $table->timestamp('cod_collected_at')->nullable()->after('cod_collected_amount');
            }
            if (!Schema::hasColumn('orders', 'cod_verified_at')) {
                $table->timestamp('cod_verified_at')->nullable()->after('cod_collected_at');
            }
            if (!Schema::hasColumn('orders', 'cod_verified_by')) {
                $table->foreignId('cod_verified_by')->nullable()->constrained('users')->onDelete('set null')->after('cod_verified_at');
            }
            if (!Schema::hasColumn('orders', 'cod_status')) {
                $table->string('cod_status')->default('pending')->after('cod_verified_by');
            }
            if (!Schema::hasColumn('orders', 'delivery_charge')) {
                $table->decimal('delivery_charge', 12, 2)->default(0)->after('cod_status');
            }
            if (!Schema::hasColumn('orders', 'admin_margin')) {
                $table->decimal('admin_margin', 12, 2)->default(0)->after('delivery_charge');
            }
            if (!Schema::hasColumn('orders', 'seller_amount')) {
                $table->decimal('seller_amount', 12, 2)->default(0)->after('admin_margin');
            }
            if (!Schema::hasColumn('orders', 'rider_amount')) {
                $table->decimal('rider_amount', 12, 2)->default(0)->after('seller_amount');
            }
            if (!Schema::hasColumn('orders', 'margin_amount')) {
                $table->decimal('margin_amount', 12, 2)->default(0)->after('rider_amount');
            }
            if (!Schema::hasColumn('orders', 'settlement_status')) {
                $table->string('settlement_status')->default('pending')->after('margin_amount');
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'cod_amount', 'cod_invoice_file', 'cod_collected_amount',
                'cod_collected_at', 'cod_verified_at', 'cod_verified_by',
                'cod_status', 'delivery_charge', 'admin_margin',
                'seller_amount', 'rider_amount', 'margin_amount',
                'settlement_status'
            ]);
        });
    }
};