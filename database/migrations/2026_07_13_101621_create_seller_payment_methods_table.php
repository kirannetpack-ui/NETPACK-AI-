<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seller_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('method_type'); // bank, esewa, khalti, connectips
            $table->boolean('is_default')->default(false);
            $table->boolean('is_verified')->default(false);
            
            // Common fields
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            
            // Bank specific
            $table->string('bank_name')->nullable();
            $table->string('branch')->nullable();
            $table->string('account_type')->nullable(); // savings, current
            
            // eSewa/Khalti specific
            $table->string('mobile_number')->nullable();
            $table->string('esewa_id')->nullable();
            $table->string('khalti_id')->nullable();
            $table->string('connectips_id')->nullable();
            
            // Verification
            $table->string('verification_document')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_default']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('seller_payment_methods');
    }
};