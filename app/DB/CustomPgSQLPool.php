<?php

namespace App\DB;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\DB\PgSQL\PgSQLPool;
use Hyperf\DB\PgSQL\Swoole4PgSQLConnection;

class CustomPgSQLPool extends PgSQLPool
{
    protected function createConnection(): ConnectionInterface
    {
        if (SWOOLE_MAJOR_VERSION < 5) {
            return new Swoole4PgSQLConnection($this->container, $this, $this->config);
        }
        return new CustomPgSQLConnection($this->container, $this, $this->config);
    }
}