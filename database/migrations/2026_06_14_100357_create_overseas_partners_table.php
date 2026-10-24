<?php
// database/migrations/xxxx_xx_xx_000001_create_overseas_partners_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('overseas_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('country');
            $table->string('city');
            $table->string('address');
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('contact_person');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('overseas_partners');
    }
};