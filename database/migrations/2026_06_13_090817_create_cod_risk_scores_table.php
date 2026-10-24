<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_cod_risk_scores_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cod_risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->decimal('cod_acceptance_rate', 5, 2)->default(0);
            $table->integer('successful_cod_count')->default(0);
            $table->integer('failed_cod_count')->default(0);
            $table->integer('returned_cod_count')->default(0);
            $table->decimal('risk_score', 5, 2)->default(0);
            $table->enum('cod_eligibility', ['allowed', 'restricted', 'blocked'])->default('allowed');
            $table->decimal('cod_limit', 12, 2)->default(50000);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cod_risk_scores');
    }
};