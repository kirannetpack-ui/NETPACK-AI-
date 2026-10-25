<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

final class SqlDate
{
    public static function year(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y', created_at)",
            'pgsql' => 'EXTRACT(YEAR FROM created_at)',
            default => 'YEAR(created_at)',
        };
    }

    public static function month(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%m', created_at)",
            'pgsql' => 'EXTRACT(MONTH FROM created_at)',
            default => 'MONTH(created_at)',
        };
    }
}
