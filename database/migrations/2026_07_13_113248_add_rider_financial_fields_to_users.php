<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'rider_deposit_balance')) {
                $table->decimal('rider_deposit_balance', 12, 2)->default(0)->after('rating');
            }
            if (!Schema::hasColumn('users', 'rider_deposit_limit')) {
                $table->decimal('rider_deposit_limit', 12, 2)->default(50000)->after('rider_deposit_balance');
            }
            if (!Schema::hasColumn('users', 'rider_commission_rate')) {
                $table->decimal('rider_commission_rate', 5, 2)->default(10)->after('rider_deposit_limit');
            }
            if (!Schema::hasColumn('users', 'rider_delivery_fee')) {
                $table->decimal('rider_delivery_fee', 10, 2)->default(100)->after('rider_commission_rate');
            }
            if (!Schema::hasColumn('users', 'rider_margin_rate')) {
                $table->decimal('rider_margin_rate', 5, 2)->default(15)->after('rider_delivery_fee');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'rider_deposit_balance',
                'rider_deposit_limit',
                'rider_commission_rate',
                'rider_delivery_fee',
                'rider_margin_rate'
            ]);
        });
    }
};