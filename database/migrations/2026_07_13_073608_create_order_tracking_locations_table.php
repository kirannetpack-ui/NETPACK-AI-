<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_tracking_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('rider_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy', 10, 2)->nullable();
            $table->decimal('speed', 10, 2)->nullable();
            $table->decimal('bearing', 10, 2)->nullable();
            $table->decimal('altitude', 10, 2)->nullable();
            $table->string('location_type')->default('gps'); // gps, network, mock
            $table->string('status')->default('active');
            $table->timestamp('timestamp');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['order_id', 'timestamp']);
            $table->index(['rider_id', 'timestamp']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_tracking_locations');
    }
};