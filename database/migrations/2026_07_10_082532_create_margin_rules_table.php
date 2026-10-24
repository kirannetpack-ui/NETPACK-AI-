<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('margin_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overseas_partner_id')->constrained('users')->onDelete('cascade');
            $table->string('rule_name');
            $table->enum('margin_type', ['percentage', 'flat']);
            $table->decimal('margin_value', 10, 2);
            $table->decimal('weight_from', 10, 2)->default(0);
            $table->decimal('weight_to', 10, 2)->nullable();
            $table->json('applicable_countries')->nullable();
            $table->json('applicable_services')->nullable();
            $table->boolean('apply_to_sub_rates')->default(false);
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->index(['overseas_partner_id', 'is_active']);
            $table->index(['weight_from', 'weight_to']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('margin_rules');
    }
};