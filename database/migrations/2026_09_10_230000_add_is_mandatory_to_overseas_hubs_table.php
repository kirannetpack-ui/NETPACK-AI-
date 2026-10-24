<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('overseas_hubs')) {
            Schema::table('overseas_hubs', function (Blueprint $table) {
                if (!Schema::hasColumn('overseas_hubs', 'is_mandatory')) {
                    $table->boolean('is_mandatory')->default(false)->after('is_active');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('overseas_hubs')) {
            Schema::table('overseas_hubs', function (Blueprint $table) {
                if (Schema::hasColumn('overseas_hubs', 'is_mandatory')) {
                    $table->dropColumn('is_mandatory');
                }
            });
        }
    }
};
