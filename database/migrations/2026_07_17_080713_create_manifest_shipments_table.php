<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('manifest_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifest_id')->constrained()->onDelete('cascade');
            $table->foreignId('bag_id')->nullable()->constrained('manifest_bags')->onDelete('set null');
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('partner_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Status tracking
            $table->string('status')->default('pending'); // pending, received, delivered, dispatched
            
            // Delivery type
            $table->string('delivery_type')->default('door_delivery'); // door_delivery, collection
            $table->boolean('is_collected')->default(false);
            $table->timestamp('collected_at')->nullable();
            
            // Payment
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->string('payment_status')->default('pending'); // pending, paid, pre_defined, nil
            
            // Dates
            $table->timestamp('received_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['manifest_id', 'status']);
            $table->index(['shipment_id', 'partner_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('manifest_shipments');
    }
};