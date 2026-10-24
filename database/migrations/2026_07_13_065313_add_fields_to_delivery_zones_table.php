<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_zones', 'partner_id')) {
                $table->foreignId('partner_id')->nullable()->after('id')->constrained('domestic_partners')->onDelete('set null');
            }
            if (!Schema::hasColumn('delivery_zones', 'admin_id')) {
                $table->foreignId('admin_id')->nullable()->after('partner_id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('delivery_zones', 'zone_type')) {
                $table->string('zone_type')->default('partner')->after('zone_code');
            }
            if (!Schema::hasColumn('delivery_zones', 'parent_zone_id')) {
                $table->foreignId('parent_zone_id')->nullable()->after('admin_id')->constrained('delivery_zones')->onDelete('set null');
            }
            if (!Schema::hasColumn('delivery_zones', 'approval_status')) {
                $table->string('approval_status')->default('pending')->after('is_active'); // pending, approved, rejected
            }
            if (!Schema::hasColumn('delivery_zones', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('delivery_zones', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('delivery_zones', 'districts')) {
                $table->json('districts')->nullable();
            }
            if (!Schema::hasColumn('delivery_zones', 'municipalities')) {
                $table->json('municipalities')->nullable();
            }
            if (!Schema::hasColumn('delivery_zones', 'wards')) {
                $table->json('wards')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->dropForeign(['partner_id']);
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['parent_zone_id']);
            $table->dropColumn(['partner_id', 'admin_id', 'zone_type', 'parent_zone_id', 'approval_status', 'approved_at', 'rejection_reason', 'districts', 'municipalities', 'wards']);
        });
    }
};
