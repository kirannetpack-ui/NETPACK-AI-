<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shipments', function (Blueprint $table) {
            if (!Schema::hasColumn('shipments', 'current_location')) {
                $table->string('current_location')->nullable()->after('status');
            }
            if (!Schema::hasColumn('shipments', 'current_latitude')) {
                $table->decimal('current_latitude', 10, 8)->nullable()->after('current_location');
            }
            if (!Schema::hasColumn('shipments', 'current_longitude')) {
                $table->decimal('current_longitude', 11, 8)->nullable()->after('current_latitude');
            }
            if (!Schema::hasColumn('shipments', 'status_notes')) {
                $table->text('status_notes')->nullable()->after('tracking_history');
            }
        });
    }

    public function down()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['current_latitude', 'current_longitude', 'current_location', 'status_notes']);
        });
    }
};
