<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'business_name')) {
                $table->string('business_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'business_address')) {
                $table->text('business_address')->nullable()->after('business_name');
            }
            if (!Schema::hasColumn('users', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('business_address');
            }
            if (!Schema::hasColumn('users', 'account_holder_name')) {
                $table->string('account_holder_name')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('users', 'account_number')) {
                $table->string('account_number')->nullable()->after('account_holder_name');
            }
            if (!Schema::hasColumn('users', 'account_type')) {
                $table->string('account_type')->nullable()->after('account_number');
            }
            if (!Schema::hasColumn('users', 'ifsc_code')) {
                $table->string('ifsc_code')->nullable()->after('account_type');
            }
            if (!Schema::hasColumn('users', 'email_notifications')) {
                $table->boolean('email_notifications')->default(true)->after('ifsc_code');
            }
            if (!Schema::hasColumn('users', 'sms_notifications')) {
                $table->boolean('sms_notifications')->default(false)->after('email_notifications');
            }
            if (!Schema::hasColumn('users', 'order_updates')) {
                $table->boolean('order_updates')->default(true)->after('sms_notifications');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'business_name', 'business_address', 'bank_name',
                'account_holder_name', 'account_number', 'account_type',
                'ifsc_code', 'email_notifications', 'sms_notifications',
                'order_updates'
            ]);
        });
    }
};