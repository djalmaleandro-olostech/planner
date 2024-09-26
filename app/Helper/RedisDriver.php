<?php

namespace App\Helper;

use Hyperf\Redis\Redis;
use Hyperf\Redis\RedisFactory;
use Hyperf\Context\ApplicationContext;

/**
 * @method keys(string $string)
 * @method del(mixed $key)
 * @method hkeys(string $string)
 * @method hexists(string $string, string $param)
 * @method hmset(string $string, array $nAmbassadors)
 * @method expire(string $string, int $int)
 * @method rpush(string $string, $param, array $order)
 */
class RedisDriver
{
    private Redis $readConnection;
    private Redis $writeConnection;

    public function __construct()
    {
        $container = ApplicationContext::getContainer();
        $this->readConnection = $container->get(RedisFactory::class)->get('default');
        $this->writeConnection = $container->get(RedisFactory::class)->get('default');
    }

    public function __call($command, $args)
    {
        $writeCommands = [
            'set', 'getset', 'mset', 'msetnx', 'incr', 'incrby', 'decr', 'decrby', 'del',
            'hset', 'hmset', 'hincrby', 'lpush', 'rpush', 'lpop', 'rpop', 'sadd', 'zadd',
            'rename', 'setex', 'expire'

        ];


        if (in_array(strtolower($command), $writeCommands)) {
            return $this->writeCommand($command, ...$args);
        }

        return $this->readCommand($command, ...$args);
    }

    public function writeCommand($command, ...$args) {
        return $this->writeConnection->{$command}(...$args);
    }

    public function readCommand($command, ...$args) {
        return $this->readConnection->{$command}(...$args);
    }
}