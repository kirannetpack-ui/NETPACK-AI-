<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manifest_shipment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifest_shipment_id')->constrained()->cascadeOnDelete();
            $table->string('event_type');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->foreignId('from_partner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_partner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('performed_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['manifest_shipment_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manifest_shipment_events');
    }
};
