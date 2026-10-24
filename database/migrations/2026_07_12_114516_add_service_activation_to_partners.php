<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Service activation flags for partners
            $table->boolean('flash_active')->default(false)->after('verification_status');
            $table->boolean('same_day_active')->default(false)->after('flash_active');
            $table->boolean('standard_active')->default(true)->after('same_day_active');
            $table->boolean('himalayan_active')->default(false)->after('standard_active');
            $table->boolean('ecommerce_active')->default(false)->after('himalayan_active');
            $table->boolean('grocery_active')->default(false)->after('ecommerce_active');
            
            // Partner settings
            $table->string('default_currency', 10)->default('NPR')->after('grocery_active');
            $table->json('service_settings')->nullable()->after('default_currency');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'flash_active',
                'same_day_active',
                'standard_active',
                'himalayan_active',
                'ecommerce_active',
                'grocery_active',
                'default_currency',
                'service_settings'
            ]);
        });
    }
};