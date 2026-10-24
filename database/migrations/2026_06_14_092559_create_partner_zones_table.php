<?php
// database/migrations/xxxx_xx_xx_000002_create_partner_zones_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('partner_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('domestic_partners')->onDelete('cascade');
            $table->string('zone_name');
            $table->json('districts'); // ['Kathmandu', 'Lalitpur', 'Bhaktapur']
            $table->json('municipalities')->nullable();
            $table->json('wards')->nullable();
            $table->enum('zone_type', ['urban', 'semi_urban', 'rural', 'hilly', 'himalayan']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('partner_zones');
    }
};