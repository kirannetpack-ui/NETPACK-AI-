<?php
// database/migrations/xxxx_xx_xx_000003_add_delay_fields_to_pickup_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->boolean('is_delayed')->default(false);
            $table->text('delay_reason')->nullable();
            $table->timestamp('delay_reported_at')->nullable();
            $table->boolean('customer_notified')->default(false);
            $table->text('customer_notification_message')->nullable();
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_phone')->nullable();
        });
    }

    public function down()
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropColumn([
                'is_delayed', 'delay_reason', 'delay_reported_at', 
                'customer_notified', 'customer_notification_message',
                'contact_person_name', 'contact_person_phone'
            ]);
        });
    }
};