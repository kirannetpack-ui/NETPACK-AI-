<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('overseas_hubs')) {
            return;
        }

        Schema::create('overseas_hubs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('hub_name');
            $table->string('hub_code')->unique();
            $table->string('location')->default('Transit Hub');
            $table->enum('hub_type', ['main_hub', 'transit_point', 'sorting_center', 'delivery_hub'])->default('main_hub');
            $table->string('address')->default('Airport Cargo Terminal');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('overseas_hubs');
    }
};
