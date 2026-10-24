<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('manifest_tracking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifest_id')->constrained()->onDelete('cascade');
            $table->foreignId('bag_id')->nullable()->constrained('manifest_bags')->onDelete('set null');
            $table->foreignId('shipment_id')->nullable()->constrained()->onDelete('set null');
            $table->string('event_type'); // created, scanned, sorted, dispatched, received, delivered
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['manifest_id', 'event_type']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('manifest_tracking_logs');
    }
};