<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_reminders', function (Blueprint $table) {
            $table->unsignedBigInteger('pickup_request_id')->nullable()->change();
            $table->foreignId('manifest_id')->nullable()->after('pickup_request_id')->constrained('manifests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('delivery_reminders', function (Blueprint $table) {
            $table->dropForeign(['manifest_id']);
            $table->dropColumn('manifest_id');
        });
    }
};
