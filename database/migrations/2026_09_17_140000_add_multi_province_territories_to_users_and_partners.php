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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'operating_provinces')) {
                $table->json('operating_provinces')->nullable()->after('province');
            }
            if (!Schema::hasColumn('users', 'operating_districts')) {
                $table->json('operating_districts')->nullable()->after('operating_provinces');
            }
        });

        Schema::table('domestic_partners', function (Blueprint $table) {
            if (!Schema::hasColumn('domestic_partners', 'operating_provinces')) {
                $table->json('operating_provinces')->nullable()->after('province');
            }
            if (!Schema::hasColumn('domestic_partners', 'operating_districts')) {
                $table->json('operating_districts')->nullable()->after('operating_provinces');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'operating_districts')) {
                $table->dropColumn('operating_districts');
            }
            if (Schema::hasColumn('users', 'operating_provinces')) {
                $table->dropColumn('operating_provinces');
            }
        });

        Schema::table('domestic_partners', function (Blueprint $table) {
            if (Schema::hasColumn('domestic_partners', 'operating_districts')) {
                $table->dropColumn('operating_districts');
            }
            if (Schema::hasColumn('domestic_partners', 'operating_provinces')) {
                $table->dropColumn('operating_provinces');
            }
        });
    }
};
