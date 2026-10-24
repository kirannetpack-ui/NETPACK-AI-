<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_tracking_locations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tracking_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('location_name')->nullable();
            $table->string('status')->nullable();
            $table->decimal('speed', 8, 2)->nullable();
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            $table->index(['shipment_id', 'recorded_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tracking_locations');
    }
};