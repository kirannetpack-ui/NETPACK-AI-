<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_settlements_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_intent_id')->constrained()->onDelete('cascade');
            $table->foreignId('recipient_user_id')->constrained('users');
            $table->enum('recipient_type', ['seller', 'rider', 'netpack']);
            $table->decimal('amount', 12, 2);
            $table->string('payout_method'); // bank_transfer, khalti, esewa, wallet
            $table->string('payout_reference')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('retry_count')->default(0);
            $table->text('failure_reason')->nullable();
            $table->timestamp('initiated_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['recipient_user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('settlements');
    }
};