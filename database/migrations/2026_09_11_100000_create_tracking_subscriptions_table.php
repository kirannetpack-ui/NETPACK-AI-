<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('tracking_subscriptions')) {
            Schema::create('tracking_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->string('tracking_number')->index();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->boolean('notify_email')->default(true);
                $table->boolean('notify_sms')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['tracking_number', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_subscriptions');
    }
};
