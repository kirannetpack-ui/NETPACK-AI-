<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('reminder_logs')) {
            return;
        }

        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_request_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('reminder_id')->nullable()->constrained('delivery_reminders')->onDelete('cascade');
            $table->string('reminder_type'); // partner, admin, customer, system, delay_alert
            $table->string('sent_to')->nullable();
            $table->text('message');
            $table->string('channel')->default('database'); // email, sms, push, database
            $table->string('status')->default('sent'); // pending, sent, failed
            $table->timestamp('sent_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['pickup_request_id', 'reminder_type']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reminder_logs');
    }
};
