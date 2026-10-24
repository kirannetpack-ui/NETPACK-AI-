<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_partner_document_fields_to_domestic_partners_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('domestic_partners', function (Blueprint $table) {
            $table->string('vat_pan_certificate')->nullable();
            $table->string('owner_id_front')->nullable();
            $table->string('owner_id_back')->nullable();
        });
    }

    public function down()
    {
        Schema::table('domestic_partners', function (Blueprint $table) {
            $table->dropColumn(['vat_pan_certificate', 'owner_id_front', 'owner_id_back']);
        });
    }
};