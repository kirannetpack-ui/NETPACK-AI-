<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('overseas_transit_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['hub', 'transit'])->default('transit');
            $table->string('location');
            $table->string('country');
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['partner_id', 'type']);
            // Ensure only one hub per partner
            $table->unique(['partner_id', 'type'])->where('type', 'hub');
        });

        // Pivot table for shipment transit points
        Schema::create('shipment_transit_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('transit_point_id')->constrained('overseas_transit_points')->onDelete('cascade');
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('departed_at')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->integer('sequence')->default(0);
            $table->timestamps();

            $table->index(['shipment_id', 'transit_point_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipment_transit_points');
        Schema::dropIfExists('overseas_transit_points');
    }
};