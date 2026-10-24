<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_online')) {
                $table->boolean('is_online')->default(false)->after('verification_status');
            }
            if (!Schema::hasColumn('users', 'is_available')) {
                $table->boolean('is_available')->default(false)->after('is_online');
            }
            if (!Schema::hasColumn('users', 'vehicle_type')) {
                $table->string('vehicle_type')->nullable()->after('is_available');
            }
            if (!Schema::hasColumn('users', 'vehicle_number')) {
                $table->string('vehicle_number')->nullable()->after('vehicle_type');
            }
            if (!Schema::hasColumn('users', 'current_latitude')) {
                $table->decimal('current_latitude', 10, 8)->nullable()->after('vehicle_number');
            }
            if (!Schema::hasColumn('users', 'current_longitude')) {
                $table->decimal('current_longitude', 11, 8)->nullable()->after('current_latitude');
            }
            if (!Schema::hasColumn('users', 'last_location_update')) {
                $table->timestamp('last_location_update')->nullable()->after('current_longitude');
            }
            if (!Schema::hasColumn('users', 'total_deliveries')) {
                $table->integer('total_deliveries')->default(0)->after('last_location_update');
            }
            if (!Schema::hasColumn('users', 'total_earnings')) {
                $table->decimal('total_earnings', 12, 2)->default(0)->after('total_deliveries');
            }
            if (!Schema::hasColumn('users', 'rating')) {
                $table->decimal('rating', 3, 2)->default(0)->after('total_earnings');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_online', 'is_available', 'vehicle_type', 'vehicle_number',
                'current_latitude', 'current_longitude', 'last_location_update',
                'total_deliveries', 'total_earnings', 'rating'
            ]);
        });
    }
};