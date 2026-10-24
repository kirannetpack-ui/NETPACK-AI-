<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Check if columns exist before adding them
            
            // Add nationality if it doesn't exist
            if (!Schema::hasColumn('users', 'nationality')) {
                $table->string('nationality')->nullable()->after('email');
            }
            
            // Add dob if it doesn't exist
            if (!Schema::hasColumn('users', 'dob')) {
                $table->date('dob')->nullable()->after('phone');
            }
            
            // Add gender if it doesn't exist
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable()->after('dob');
            }
            
            // Add permanent_address if it doesn't exist
            if (!Schema::hasColumn('users', 'permanent_address')) {
                $table->text('permanent_address')->nullable()->after('gender');
            }
            
            // Add temporary_address if it doesn't exist
            if (!Schema::hasColumn('users', 'temporary_address')) {
                $table->text('temporary_address')->nullable()->after('permanent_address');
            }
            
            // Add address_lat if it doesn't exist
            if (!Schema::hasColumn('users', 'address_lat')) {
                $table->decimal('address_lat', 10, 8)->nullable()->after('temporary_address');
            }
            
            // Add address_lng if it doesn't exist
            if (!Schema::hasColumn('users', 'address_lng')) {
                $table->decimal('address_lng', 11, 8)->nullable()->after('address_lat');
            }
            
            // Add profile_photo if it doesn't exist
            if (!Schema::hasColumn('users', 'profile_photo')) {
                $table->string('profile_photo')->nullable()->after('address_lng');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'nationality',
                'dob',
                'gender',
                'permanent_address',
                'temporary_address',
                'address_lat',
                'address_lng',
                'profile_photo'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};