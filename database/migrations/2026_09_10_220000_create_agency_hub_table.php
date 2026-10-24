<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('agency_hub')) {
            Schema::create('agency_hub', function (Blueprint $table) {
                $table->id();
                $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
                $table->foreignId('hub_id')->constrained('overseas_hubs')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['agency_id', 'hub_id']);
                $table->index(['hub_id', 'agency_id']);
            });
        }

        // Migrate existing agencies.hub_id relationships into agency_hub pivot
        if (Schema::hasTable('agencies') && Schema::hasColumn('agencies', 'hub_id')) {
            $existingAgencies = DB::table('agencies')->whereNotNull('hub_id')->get(['id', 'hub_id']);
            $now = now();

            foreach ($existingAgencies as $agency) {
                $exists = DB::table('agency_hub')
                    ->where('agency_id', $agency->id)
                    ->where('hub_id', $agency->hub_id)
                    ->exists();

                if (!$exists) {
                    // Only insert if hub still exists in overseas_hubs
                    $hubExists = DB::table('overseas_hubs')->where('id', $agency->hub_id)->exists();
                    if ($hubExists) {
                        DB::table('agency_hub')->insert([
                            'agency_id' => $agency->id,
                            'hub_id' => $agency->hub_id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_hub');
    }
};
