<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_agency_staff_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('agency_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone');
            $table->string('position'); // Manager, Scanner, Logistics, Admin
            $table->enum('role', ['admin', 'scanner', 'viewer'])->default('scanner');
            $table->boolean('can_scan_arrival')->default(true);
            $table->boolean('can_scan_departure')->default(false);
            $table->boolean('can_add_notes')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agency_staff');
    }
};