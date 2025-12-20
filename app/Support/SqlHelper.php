<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class SqlHelper
{
    public static function lowerWithPadding(string $column): string
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb', 'sqlsrv'], true)) {
            return "concat(' ', lower($column), ' ')";
        }

        return "(' ' || lower($column) || ' ')";
    }
}
