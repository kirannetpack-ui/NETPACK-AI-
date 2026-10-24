<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('vehicle_type')->nullable();
            $table->string('license_number')->nullable();
            $table->boolean('is_available')->default(true);
            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            $table->string('device_token')->nullable();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->string('delivery_proof_image')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'vehicle_type', 'license_number', 'is_available',
                'current_latitude', 'current_longitude', 'device_token',
                'rating', 'delivery_proof_image'
            ]);
        });
    }
};