<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->enum('user_type', ['admin', 'seller', 'rider', 'client', 'partner', 'overseas', 'staff', 'customer', 'domestic'])
                      ->default('customer')
                      ->after('email');
            }
            
            if (!Schema::hasColumn('users', 'verification_status')) {
                $table->enum('verification_status', ['pending', 'approved', 'rejected', 'suspended'])
                      ->default('pending')
                      ->after('user_type');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_type', 'verification_status']);
        });
    }
};