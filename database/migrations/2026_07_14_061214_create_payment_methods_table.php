<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('user_type')->default('seller');
            $table->string('method_type');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch')->nullable();
            $table->string('account_type')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('esewa_id')->nullable();
            $table->string('khalti_id')->nullable();
            $table->string('connectips_id')->nullable();
            $table->string('verification_document')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'user_type', 'is_default']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_methods');
    }
};