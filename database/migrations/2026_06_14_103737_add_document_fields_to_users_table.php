<?php
// database/migrations/xxxx_xx_xx_000001_add_document_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address_proof')->nullable();
            $table->text('business_license')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('verification_token')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'address_proof', 'business_license',
                'rejection_reason', 'verified_at', 'verification_token'
            ]);
        });
    }
};
