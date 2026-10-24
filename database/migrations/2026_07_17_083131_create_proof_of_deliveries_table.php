<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('proof_of_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifest_shipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('manifest_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('pod_type'); // file, photo
            $table->string('pod_file')->nullable();
            $table->string('pod_photo')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_signature')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('status')->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['manifest_shipment_id', 'status']);
            $table->index('shipment_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('proof_of_deliveries');
    }
};