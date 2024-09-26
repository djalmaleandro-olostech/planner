<?php


namespace App\Helper;

use App\Exception\ConnectionException;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Context\ApplicationContext;
use Swoole\Coroutine\PostgreSQL;
use Hyperf\Config\ConfigFactory;

class MyConnection implements ConnectionInterface
{
    private $params;
    private $config;

    function __construct(){
        $config = new ConfigFactory;
        $this->config = $config(ApplicationContext::getContainer());

        $this->params = [
            'dbname' => $this->config->get("databases.default.database"),
            'user' => $this->config->get("databases.default.username"),
            'password' => $this->config->get("databases.default.password"),
            'port' => $this->config->get("databases.default.port"),
            'host' => $this->config->get("databases.default.host"),
        ];
    }

    public function getConnection(): PostgreSQL
    {
        $pgsql = new PostgreSQL();

        if (!$pgsql->connect($this->dsn($this->params))) {
            throw ConnectionException::failed($this->dsn($this->params));
        }

        return $pgsql;
    }

    public function reconnect(): bool
    {
        return true;
        // TODO: Implement reconnect() method.
    }

    public function check(): bool
    {
        return true;
        // TODO: Implement check() method.
    }

    public function close(): bool
    {
        return true;
        // TODO: Implement close() method.
    }

    public function release(): void
    {
        // TODO: Implement release() method.
    }

    private function dsn(array $params): string
    {
        return implode(';', [
            "host={$params['host']}",
            "port={$params['port']}",
            "dbname={$params['dbname']}",
            "user={$params['user']}",
            "password={$params['password']}",
        ]);
    }
}