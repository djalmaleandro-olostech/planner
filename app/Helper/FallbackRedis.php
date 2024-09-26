<?php

namespace App\Helper;

use Hyperf\Redis\Redis;

class FallbackRedis extends Redis
{
    protected string $poolName = 'fallback';
}