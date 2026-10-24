<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('delivery_reminders')) {
            return;
        }

        Schema::create('delivery_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_request_id')->constrained()->onDelete('cascade');
            $table->string('service_tier')->nullable();
            $table->string('reminder_type'); // partner, admin, customer
            $table->integer('reminder_number')->default(1);
            $table->timestamp('scheduled_at');
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['pickup_request_id', 'is_sent']);
            $table->index('scheduled_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_reminders');
    }
};
