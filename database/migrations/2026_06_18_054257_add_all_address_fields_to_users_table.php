<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllAddressFieldsToUsersTable extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add all columns here
            if (!Schema::hasColumn('users', 'permanent_address')) {
                $table->text('permanent_address')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'temporary_address')) {
                $table->text('temporary_address')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'address_lat')) {
                $table->decimal('address_lat', 10, 8)->nullable();
            }
            
            if (!Schema::hasColumn('users', 'address_lng')) {
                $table->decimal('address_lng', 11, 8)->nullable();
            }
            
            if (!Schema::hasColumn('users', 'profile_photo')) {
                $table->string('profile_photo')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'dob')) {
                $table->string('dob')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'nationality')) {
                $table->string('nationality')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'permanent_address',
                'temporary_address',
                'address_lat',
                'address_lng',
                'profile_photo',
                'dob',
                'gender',
                'nationality'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}