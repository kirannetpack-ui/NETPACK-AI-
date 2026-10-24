<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cod_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('rider_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Amounts
            $table->decimal('cod_amount', 12, 2);
            $table->decimal('delivery_charge', 12, 2)->default(0);
            $table->decimal('admin_margin', 12, 2)->default(0);
            $table->decimal('seller_amount', 12, 2);
            $table->decimal('rider_amount', 12, 2);
            $table->decimal('margin_amount', 12, 2);
            
            // Status
            $table->string('settlement_status')->default('pending');
            $table->timestamp('settlement_date')->nullable();
            $table->string('settlement_reference')->nullable();
            
            // Files
            $table->string('invoice_file')->nullable();
            $table->string('collection_proof')->nullable();
            
            // Timestamps
            $table->timestamp('collected_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->text('remarks')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['order_id', 'settlement_status']);
            $table->index('settlement_reference');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cod_settlements');
    }
};