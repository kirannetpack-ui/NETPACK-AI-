<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Make hawb_number nullable
            $table->string('hawb_number')->nullable()->change();
            // Remove unique constraint if exists
            $table->dropUnique('shipments_hawb_number_unique');
        });
    }

    public function down()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('hawb_number')->nullable(false)->change();
            $table->unique('hawb_number');
        });
    }
};