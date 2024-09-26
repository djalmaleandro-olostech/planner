<?php

namespace App\DB;

use Hyperf\DB\Exception\QueryException;
use Hyperf\DB\PgSQL\PgSQLConnection;
use Swoole\Coroutine\PostgreSQLStatement;

class CustomPgSQLConnection extends PgSQLConnection
{
    protected function prepare(string $query): PostgreSQLStatement
    {

        $query = preg_replace_callback('/\?+(?!\?)/', function ($matches) {
            if (isset($matches[0]) && $matches[0] === "??") {
                return "#ESC";
            }
            static $count = 0;
            $count++;
            return '$' . $count;
        }, $query);

        $query = str_replace('#ESC', '?', $query);

        $statement = $this->connection->prepare($query);
        if (! $statement) {
            throw new QueryException($this->connection->error);
        }

        return $statement;
    }
}