<?php
// database/migrations/xxxx_xx_xx_000004_create_partner_staff_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('partner_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('domestic_partners')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone');
            $table->string('position');
            $table->enum('role', ['admin', 'scanner', 'delivery_boy', 'dispatcher']);
            $table->boolean('can_scan_arrival')->default(false);
            $table->boolean('can_scan_departure')->default(false);
            $table->boolean('can_scan_delivery')->default(false);
            $table->boolean('can_add_notes')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('partner_staff');
    }
};