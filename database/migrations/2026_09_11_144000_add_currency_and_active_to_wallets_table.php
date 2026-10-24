<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            if (!Schema::hasColumn('wallets', 'currency')) {
                $table->string('currency', 10)->default('NPR')->after('total_withdrawn');
            }
            if (!Schema::hasColumn('wallets', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('currency');
            }
            if (!Schema::hasColumn('wallets', 'metadata')) {
                $table->json('metadata')->nullable()->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('wallets', 'metadata')) {
                $columnsToDrop[] = 'metadata';
            }
            if (Schema::hasColumn('wallets', 'is_active')) {
                $columnsToDrop[] = 'is_active';
            }
            if (Schema::hasColumn('wallets', 'currency')) {
                $columnsToDrop[] = 'currency';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
