<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('user_type')->default('customer');
            $table->string('verification_status')->default('pending');
            $table->string('business_name')->nullable();
            $table->string('business_address')->nullable();
            $table->string('citizenship_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('citizenship_front')->nullable();
            $table->string('citizenship_back')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('driving_license_front')->nullable();
            $table->string('driving_license_back')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};