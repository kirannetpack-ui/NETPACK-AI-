<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proof_of_deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('proof_of_deliveries', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('proof_of_deliveries', 'verified_at')) {
                $table->timestamp('verified_at')->nullable();
            }

            if (!Schema::hasColumn('proof_of_deliveries', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('proof_of_deliveries', function (Blueprint $table) {
            if (Schema::hasColumn('proof_of_deliveries', 'verified_by')) {
                $table->dropConstrainedForeignId('verified_by');
            }

            if (Schema::hasColumn('proof_of_deliveries', 'verified_at')) {
                $table->dropColumn('verified_at');
            }

            if (Schema::hasColumn('proof_of_deliveries', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};
