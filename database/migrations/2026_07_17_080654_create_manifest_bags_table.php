<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('manifest_bags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifest_id')->constrained()->onDelete('cascade');
            $table->string('bag_number')->unique();
            $table->string('qr_code')->unique();
            $table->string('bag_type'); // consolidated, non_consolidated
            $table->integer('shipment_count')->default(0);
            $table->decimal('weight', 10, 2)->default(0);
            $table->string('status')->default('pending'); // pending, scanned, sorted, dispatched
            
            // Tracking
            $table->string('current_location')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamp('sorted_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['manifest_id', 'bag_number']);
            $table->index('qr_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('manifest_bags');
    }
};