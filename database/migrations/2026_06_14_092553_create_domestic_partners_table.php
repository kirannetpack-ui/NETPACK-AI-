<?php
// database/migrations/xxxx_xx_xx_000001_create_domestic_partners_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('domestic_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('company_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone');
            $table->string('address');
            $table->string('city');
            $table->string('district');
            $table->string('province');
            $table->string('pan_number')->nullable(); // Tax ID
            $table->enum('service_type', ['flash', 'same_day', 'standard', 'himalayan', 'all'])->default('all');
            $table->json('service_areas')->nullable();
            $table->decimal('margin_percentage', 5, 2)->default(10); // NETPACK margin
            $table->boolean('is_active')->default(true);
            $table->boolean('kyc_verified')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('domestic_partners');
    }
};