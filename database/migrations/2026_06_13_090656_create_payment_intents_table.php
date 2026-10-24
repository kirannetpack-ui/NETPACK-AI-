<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_payment_intents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_intents', function (Blueprint $table) {
            $table->id();
            $table->string('intent_id')->unique();
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('seller_id')->constrained('users');
            $table->foreignId('rider_id')->nullable()->constrained('users');
            
            $table->decimal('total_amount', 12, 2);
            $table->json('split_breakdown'); // Stores seller, rider, netpack amounts
            $table->json('split_percentages'); // Store percentages
            
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_gateway')->default('khalti');
            $table->string('gateway_transaction_id')->nullable();
            $table->json('gateway_response')->nullable();
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['intent_id', 'shipment_id']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_intents');
    }
};