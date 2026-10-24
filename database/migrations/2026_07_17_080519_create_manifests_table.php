<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('manifests', function (Blueprint $table) {
            $table->id();
            $table->string('manifest_number')->unique();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('partner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('load_type'); // consolidated, non_consolidated
            $table->string('status')->default('pending'); // pending, in_transit, received, dispatched, delivered
            
            // Location tracking
            $table->string('origin_city')->nullable();
            $table->string('destination_city')->nullable();
            $table->string('current_location')->nullable();
            
            // Manifest details
            $table->integer('total_bags')->default(0);
            $table->integer('total_shipments')->default(0);
            $table->decimal('total_weight', 10, 2)->default(0);
            
            // Dates
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['partner_id', 'status']);
            $table->index('manifest_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('manifests');
    }
};