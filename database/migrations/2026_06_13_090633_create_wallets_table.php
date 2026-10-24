<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_wallets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('user_type', ['seller', 'rider', 'customer', 'admin'])->default('customer');
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('pending_balance', 12, 2)->default(0);
            $table->decimal('total_earned', 12, 2)->default(0);
            $table->decimal('total_withdrawn', 12, 2)->default(0);
            
            // Bank details for payout
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('mobile_banking_number')->nullable();
            $table->string('khalti_id')->nullable();
            $table->string('esewa_id')->nullable();
            
            // KYC Status
            $table->boolean('kyc_verified')->default(false);
            $table->timestamp('kyc_verified_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'user_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('wallets');
    }
};