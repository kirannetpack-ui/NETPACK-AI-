<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('deliveries')) {
            return;
        }

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('delivery_number')->unique();
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->string('recipient_email')->nullable();
            $table->text('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('address_type')->default('home');
            $table->string('landmark')->nullable();
            $table->text('instructions')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('rider_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->string('tracking_number')->unique()->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['order_id', 'status']);
            $table->index('tracking_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
};
