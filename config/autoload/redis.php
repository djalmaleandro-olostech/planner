<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'default' => [
        'host' => Hyperf\Support\env('REDIS_HOST', 'redis-master'),
        'auth' => Hyperf\Support\env('REDIS_AUTH'),
        'port' => (int) Hyperf\Support\env('REDIS_PORT', 6379),
        'db' => (int) Hyperf\Support\env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 5,
            'max_connections' => 300,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => 20,
            'max_idle_time' => (float) Hyperf\Support\env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],

    'reader' => [
        'host' => Hyperf\Support\env('REDIS_RR_HOST', 'redis-replica'),
        'auth' => Hyperf\Support\env('REDIS_AUTH'),
        'port' => (int) Hyperf\Support\env('REDIS_PORT', 6379),
        'db' => (int) Hyperf\Support\env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 5,
            'max_connections' => 300,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => 20,
            'max_idle_time' => (float) Hyperf\Support\env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],
];