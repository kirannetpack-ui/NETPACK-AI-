<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_seller_document_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // These columns are part of the original users table migration.
    }

    public function down()
    {
        // Do not remove columns owned by the original users table migration.
    }
};
