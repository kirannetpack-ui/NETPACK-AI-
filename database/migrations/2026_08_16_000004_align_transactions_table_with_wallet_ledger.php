<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Ledger sources are application-defined (for example deposit,
            // delivery, COD settlement, payout request, and admin margin).
            $table->string('source', 50)->change();

            if (!Schema::hasColumn('transactions', 'status')) {
                $table->string('status', 20)->default('completed')->after('description');
            }

            if (!Schema::hasColumn('transactions', 'metadata')) {
                $table->json('metadata')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['status', 'metadata']);
        });
    }
};
