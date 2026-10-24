<?php
// database/migrations/xxxx_xx_xx_000002_create_reminder_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Superseded by the consolidated domestic-services migration, which
        // creates delivery_reminders before applying this foreign key.
    }

    public function down()
    {
        // The consolidated migration owns this table.
    }
};
