<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('overseas_transit_points', function (Blueprint $table) {
            // Check if columns exist before modifying
            if (Schema::hasColumn('overseas_transit_points', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('overseas_transit_points', 'city')) {
                $table->renameColumn('city', 'location');
            }
            if (!Schema::hasColumn('overseas_transit_points', 'location')) {
                $table->string('location')->nullable();
            }
            if (Schema::hasColumn('overseas_transit_points', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('overseas_transit_points', 'latitude')) {
                $table->dropColumn('latitude');
            }
            if (Schema::hasColumn('overseas_transit_points', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('overseas_transit_points', 'contact_info')) {
                $table->dropColumn('contact_info');
            }
            if (Schema::hasColumn('overseas_transit_points', 'operating_hours')) {
                $table->dropColumn('operating_hours');
            }
            if (Schema::hasColumn('overseas_transit_points', 'capabilities')) {
                $table->dropColumn('capabilities');
            }
            if (Schema::hasColumn('overseas_transit_points', 'sequence')) {
                $table->dropColumn('sequence');
            }
            
            // Make sure type is enum with correct values
            if (Schema::hasColumn('overseas_transit_points', 'type')) {
                $table->string('type')->default('transit_point')->change();
            }
        });
    }

    public function down()
    {
        // Revert changes if needed
    }
};