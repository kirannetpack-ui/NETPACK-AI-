<?php
// database/migrations/xxxx_xx_xx_000003_create_overseas_staff_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('overseas_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('overseas_partners')->onDelete('cascade');
            $table->foreignId('hub_id')->nullable()->constrained('overseas_hubs')->onDelete('set null');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone');
            $table->string('position');
            $table->enum('role', ['admin', 'scanner', 'coordinator'])->default('scanner');
            $table->boolean('can_scan_arrival')->default(true);
            $table->boolean('can_scan_departure')->default(false);
            $table->boolean('can_scan_customs')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('overseas_staff');
    }
};