<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop existing table and recreate if needed
        if (Schema::hasTable('remote_area_surcharges')) {
            Schema::dropIfExists('remote_area_surcharges');
        }

        Schema::create('remote_area_surcharges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overseas_partner_id')->constrained('users')->onDelete('cascade');
            $table->string('country');
            $table->string('zip_code_pattern'); // e.g., 995*, IV*, 10000-20000
            $table->string('area_name')->nullable();
            $table->decimal('surcharge_amount', 10, 2)->default(0);
            $table->decimal('surcharge_percentage', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['country', 'zip_code_pattern']);
            $table->index('overseas_partner_id');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('remote_area_surcharges');
    }
};